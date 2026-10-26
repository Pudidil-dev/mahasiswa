"""Uji integrasi pada database sementara; membutuhkan Python, PHP, dan mysql CLI."""
import argparse
import html
import http.cookiejar
import json
import os
from pathlib import Path
import re
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
import uuid

ROOT = Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('--mysql', default='mysql', help='Path mysql.exe atau mysql di PATH')
parser.add_argument('--php', default='php', help='Path php.exe atau php di PATH')
args = parser.parse_args()
databases = []
uploaded = set()
server = None
server_log = tempfile.TemporaryFile()
db_env = os.environ.copy()
db_env['MYSQL_PWD'] = os.environ.get('DB_PASS', '')


def sql(statement, database=None):
    command = [args.mysql, '-h', os.environ.get('DB_HOST', 'localhost'),
               '-P', os.environ.get('DB_PORT', '3306'),
               '-u', os.environ.get('DB_USER', 'root'),
               '--default-character-set=utf8mb4', '--batch', '--skip-column-names']
    result = subprocess.run(command + ([database] if database else []), input=statement,
                            text=True, encoding='utf-8', capture_output=True, env=db_env)
    if result.returncode:
        raise RuntimeError(result.stderr)
    return result.stdout.strip()


def create_database():
    name = 'uts_test_' + uuid.uuid4().hex
    sql('CREATE DATABASE ' + name + ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;')
    databases.append(name)
    return name


def import_sql(filename, database):
    statement = (ROOT / 'database' / filename).read_text(encoding='utf-8')
    statement = statement.replace('CREATE DATABASE IF NOT EXISTS mahasiswa',
                                  'CREATE DATABASE IF NOT EXISTS ' + database)
    statement = statement.replace('USE mahasiswa;', 'USE ' + database + ';')
    sql(statement, database)


def rows(document):
    body = re.search(r'<tbody id="mahasiswa-body">(.*?)</tbody>', document, re.S).group(1)
    result = []
    for row in re.findall(r'<tr>(.*?)</tr>', body, re.S):
        cells = re.findall(r'<td(?:\s[^>]*)?>(.*?)</td>', row, re.S)
        if len(cells) == 8:
            result.append([html.unescape(re.sub(r'<[^>]+>', '', c)).strip() for c in cells])
    return result


def nav(document, label):
    for classes, content in re.findall(r'<li class="page-item([^"]*)">(.*?)</li>', document, re.S):
        if html.unescape(re.sub(r'<[^>]+>', '', content)).strip() == label:
            link = re.search(r'href="([^"]+)"', content)
            return 'disabled' in classes, html.unescape(link.group(1)) if link else None
    raise AssertionError('Tombol tidak ditemukan: ' + label)


try:
    # Migrasi foto dari dua versi lama, termasuk prioritas photo atas kolom foto.
    legacy = create_database()
    sql("""CREATE TABLE nilaimhs (
        nim VARCHAR(10) PRIMARY KEY, namalengkap VARCHAR(100), foto VARCHAR(255),
        nilaiakhir DECIMAL(5,2), nilaihuruf CHAR(1), predikat VARCHAR(30),
        created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);
        CREATE TABLE photo (nim VARCHAR(10) PRIMARY KEY, namafile VARCHAR(255),
            FOREIGN KEY (nim) REFERENCES nilaimhs(nim) ON DELETE CASCADE);
        INSERT INTO nilaimhs (nim,namalengkap,foto,nilaiakhir,nilaihuruf,predikat) VALUES
            ('1111111111','Foto lama','assets/img/avatar.png',90,'A','Sangat Memuaskan'),
            ('2222222222','Foto kolom','assets/img/avatar3.png',70,'B','Memuaskan');
        INSERT INTO photo VALUES ('1111111111','assets/img/avatar2.png');""", legacy)
    original = sql('SELECT nim,namalengkap,nilaiakhir,created_date,modified_date FROM nilaimhs ORDER BY nim', legacy)
    import_sql('setup.sql', legacy)
    assert original == sql('SELECT nim,namalengkap,nilaiakhir,created_date,modified_date FROM nilaimhsw ORDER BY nim', legacy)
    assert sql('SELECT file_path FROM nilaimhsw ORDER BY nim', legacy).splitlines() == [
        'assets/img/avatar2.png', 'assets/img/avatar3.png']
    snapshot = sql('SELECT * FROM nilaimhsw ORDER BY nim', legacy)
    import_sql('setup.sql', legacy)
    assert snapshot == sql('SELECT * FROM nilaimhsw ORDER BY nim', legacy)
    print('PASS: migrasi data/foto lama dan pengulangan migrasi')

    database = create_database()
    import_sql('mahasiswa.sql', database)
    snapshot = sql('SELECT * FROM nilaimhsw ORDER BY nim', database)
    import_sql('setup.sql', database)
    import_sql('seed.sql', database)
    import_sql('seed.sql', database)
    assert snapshot == sql('SELECT * FROM nilaimhsw ORDER BY nim', database)
    source_count = int(sql('SELECT COUNT(*) FROM nilaimhsw', database))
    assert source_count > 0
    print('PASS: import dump lengkap dan seed berulang tanpa duplikasi')

    # Data tambahan hanya untuk menguji pagination di database sementara.
    # Seeder tetap memakai data asli; foto asli tidak diedit/dihapus oleh tes.
    fixture_nims = []
    grades = [(30, 'E', 'Tidak Lulus'), (45, 'D', 'Kurang'), (55, 'C', 'Cukup'),
              (65, 'B', 'Memuaskan'), (75, 'B', 'Memuaskan'),
              (85, 'A', 'Sangat Memuaskan'), (95, 'A', 'Sangat Memuaskan')]
    for index in range(max(1, 16 - source_count)):
        fixture_nim = str(8888100001 + index)
        score, letter, predicate = grades[index % len(grades)]
        sql(f"""INSERT INTO nilaimhsw (nim,namalengkap,file_path,nilaiakhir,nilaihuruf,predikat)
            VALUES ('{fixture_nim}','Uji Pagination {index + 1}','assets/img/avatar.png',
                    {score},'{letter}','{predicate}')""", database)
        fixture_nims.append(fixture_nim)

    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        port = sock.getsockname()[1]
    base = 'http://127.0.0.1:' + str(port)
    server = subprocess.Popen([args.php, '-S', '127.0.0.1:' + str(port), '-t', str(ROOT)],
                              cwd=ROOT, env=dict(os.environ, DB_NAME=database),
                              stdout=server_log, stderr=server_log,
                              creationflags=subprocess.CREATE_NO_WINDOW if os.name == 'nt' else 0)
    client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

    def request(path, fields=None, file=None, expected=200):
        headers = {}
        body = None
        if fields is not None:
            boundary = uuid.uuid4().hex
            parts = []
            for key, value in fields.items():
                parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode())
            if file:
                key, data = file
                parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"; filename="test.png"\r\nContent-Type: image/png\r\n\r\n'.encode() + data + b'\r\n')
            body = b''.join(parts) + f'--{boundary}--\r\n'.encode()
            headers['Content-Type'] = 'multipart/form-data; boundary=' + boundary
        try:
            response = client.open(urllib.request.Request(base + '/' + path, data=body, headers=headers), timeout=10)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            content = response.read()
            assert response.code == expected, (path, response.code, content[:300])
            return content

    for attempt in range(100):
        try:
            request('users.php')
            break
        except urllib.error.URLError:
            if server.poll() is not None:
                raise RuntimeError('Server PHP berhenti')
            time.sleep(0.1)
    else:
        raise RuntimeError('Server PHP tidak siap')

    def page(**params):
        return request('users.php?' + urllib.parse.urlencode(params)).decode('utf-8')

    def data():
        return {r['nim']: r for r in json.loads(request('php/mahasiswa.php'))}

    all_data = data()
    total = len(all_data)
    source_sample = next(row for nim, row in all_data.items() if nim not in fixture_nims)
    for size in (5, 10, 15):
        first = page(size=size)
        last_page = (total + size - 1) // size
        assert len(rows(first)) == size
        assert nav(first, 'First Page')[0] and nav(first, 'Previous')[0]
        assert urllib.parse.parse_qs(urllib.parse.urlsplit(nav(first, 'Last Page')[1]).query)['page'] == [str(last_page)]
        last = page(size=size, page=last_page)
        assert len(rows(last)) == total - size * (last_page - 1)
        assert nav(last, 'Last Page')[0] and nav(last, 'Next')[0]
        assert urllib.parse.parse_qs(urllib.parse.urlsplit(nav(last, 'First Page')[1]).query)['page'] == ['1']
    for sort in ('asc', 'desc'):
        found = [row for number in range(1, (total + 4) // 5 + 1)
                 for row in rows(page(size=5, page=number, sort=sort, ajax=1))]
        scores = [float(row[2]) for row in found]
        assert scores == sorted(scores, reverse=sort == 'desc')
        assert len({row[0] for row in found}) == total
    assert any(row[0] == source_sample['nim'] for row in rows(page(q=source_sample['namalengkap'].lower(), size=15)))
    for predicate in {row['predikat'] for row in all_data.values()}:
        found = rows(page(predikat=predicate, size=15))
        assert len(found) == sum(row['predikat'] == predicate for row in all_data.values())
        assert all(row[4] == predicate for row in found)
    combined = page(q='a', predikat='Memuaskan', size=5, sort='desc')
    assert rows(combined) and all('a' in row[1].lower() and row[4] == 'Memuaskan' for row in rows(combined))
    sort_link = re.search(r'<a\s+href="([^"]+)"\s+data-urutan=', combined).group(1)
    saved = urllib.parse.parse_qs(urllib.parse.urlsplit(html.unescape(sort_link)).query)
    assert all(saved[k] == [v] for k, v in {'q': 'a', 'predikat': 'Memuaskan', 'sort': 'asc', 'size': '5'}.items())
    saved = urllib.parse.parse_qs(urllib.parse.urlsplit(nav(page(q='a', size=5, sort='desc'), 'Next')[1]).query)
    assert all(saved[k] == [v] for k, v in {'q': 'a', 'sort': 'desc', 'size': '5'}.items())
    assert not rows(page(q='tidak_ada_12345'))
    assert not rows(page(q="' OR 1=1 --"))
    print('PASS: pencarian, seluruh predikat, sorting, limit 5/10/15, dan empat tombol pagination')

    document = page(size=15)
    name_cells = re.findall(r'<td>\s*<div class="mahasiswa-identitas">(.*?)</div>\s*</td>', document, re.S)
    assert len(name_cells) == 15 and all('<img' in cell and '<span>' in cell for cell in name_cells)
    for path in {row['file_path'] for row in all_data.values() if row['file_path']}:
        assert request(path)
    photo_sample = next(row for row in all_data.values() if row['file_path'])
    assert html.escape(photo_sample['namalengkap'], quote=False) in request('php/readphoto.php').decode()
    assert b'<img' in request('php/mahasiswa.php?tabel=1')
    assert b'<form' in request('php/form_upload.php')
    print('PASS: foto satu kolom dengan nama, path foto snapshot, galeri, dan endpoint referensi')

    token = re.search(r'name="token" value="([^"]+)"', document).group(1)
    nim = '8888000001'
    fields = {'nim': nim, 'nama_mhsw': 'Uji <b>Foto</b>', 'nilai_akhir': '87.90', 'token': token}
    image = (ROOT / 'assets/img/avatar.png').read_bytes()
    request('php/insertnilai.php', fields, ('foto', image))
    photo = data()[nim]['file_path']
    uploaded.add(photo)
    assert request(photo)
    assert 'Uji &lt;b&gt;Foto&lt;/b&gt;' in page(q=nim)
    request('php/insertnilai.php', fields, ('foto', image), expected=409)
    edit = dict(fields, modal='1', nama_mhsw='Uji foto edit', nilai_akhir='70.50')
    assert json.loads(request('php/editnilai.php', edit))['success']
    assert data()[nim]['file_path'] == photo
    request('php/editnilai.php', edit, ('foto', b'bukan gambar'), expected=422)
    assert data()[nim]['file_path'] == photo
    assert json.loads(request('php/editnilai.php', edit, ('foto', image)))['success']
    new_photo = data()[nim]['file_path']
    uploaded.add(new_photo)
    assert new_photo != photo and not (ROOT / photo).exists()
    request('php/upload.php', {'nim': nim, 'token': token}, ('photo', image))
    latest = data()[nim]['file_path']
    uploaded.add(latest)
    assert latest != new_photo and not (ROOT / new_photo).exists()
    request('php/deletenilai.php', {'nim': nim, 'token': token})
    assert nim not in data() and not (ROOT / latest).exists()
    request('php/deletenilai.php', {'nim': fixture_nims[0], 'token': token})
    assert (ROOT / 'assets/img/avatar.png').exists()
    print('PASS: tambah, NIM duplikat, edit, validasi upload, ganti foto, hapus, dan avatar bersama')
    print('Semua pengujian UTS lulus.')
finally:
    if server is not None:
        server.terminate()
        server.wait(timeout=10)
    server_log.close()
    for path in uploaded:
        if re.fullmatch(r'uploads/mahasiswa/[a-f0-9]{32}\.(jpg|png|gif)', path):
            target = (ROOT / path).resolve()
            assert target.parent == (ROOT / 'uploads/mahasiswa').resolve()
            target.unlink(missing_ok=True)
    for database in databases:
        assert re.fullmatch(r'uts_test_[a-f0-9]{32}', database)
        sql('DROP DATABASE ' + database)
