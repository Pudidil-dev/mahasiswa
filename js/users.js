// Elemen ini tetap ada ketika isi tabel diganti.
const formFilter = document.getElementById('filter-mahasiswa');
const kolomPencarian = document.getElementById('user-search');
const hasilMahasiswa = document.getElementById('hasil-mahasiswa');
const pesanPencarian = document.getElementById('pesan-pencarian');

let timerPencarian;
let permintaanAktif = null;
const jedaPencarian = 300; // Milidetik, yaitu 0,3 detik setelah ketikan terakhir.

function buatUrlDaftar(halaman = 1, urutan = formFilter.elements.sort.value) {
    // FormData juga membaca dropdown footer melalui atribut form.
    const parameter = new URLSearchParams(new FormData(formFilter));
    parameter.set('page', halaman);
    parameter.set('sort', urutan);

    const url = new URL(formFilter.action);
    url.search = parameter.toString();
    return url;
}

function tampilkanPesanPencarian(pesan, adaError = false) {
    pesanPencarian.textContent = pesan;
    pesanPencarian.hidden = pesan === '';
    pesanPencarian.classList.toggle('text-danger', adaError);
    pesanPencarian.classList.toggle('text-body-secondary', !adaError);
}

async function muatDaftar(url) {
    clearTimeout(timerPencarian);

    // Batalkan pencarian lama agar hasilnya tidak menimpa pencarian terbaru.
    if (permintaanAktif !== null) {
        permintaanAktif.abort();
    }
    const permintaan = new AbortController();
    permintaanAktif = permintaan;

    hasilMahasiswa.setAttribute('aria-busy', 'true');
    tampilkanPesanPencarian('Memuat data mahasiswa...');

    try {
        const urlPermintaan = new URL(url);
        urlPermintaan.searchParams.set('ajax', '1');
        const respons = await fetch(urlPermintaan, {
            signal: permintaan.signal,
            cache: 'no-store',
        });

        if (!respons.ok) {
            throw new Error('Data mahasiswa gagal dimuat.');
        }
        const htmlTabel = await respons.text();
        if (permintaan.signal.aborted) {
            return;
        }

        // Form pencarian tetap di tempatnya sehingga fokus mengetik tidak hilang.
        hasilMahasiswa.innerHTML = htmlTabel;
        formFilter.elements.sort.value = url.searchParams.get('sort') || '';
        window.history.replaceState(null, '', url);
        tampilkanPesanPencarian('');
    } catch (error) {
        if (error.name !== 'AbortError') {
            tampilkanPesanPencarian('Data gagal dimuat. Coba ketik kembali atau muat ulang halaman.', true);
        }
    } finally {
        // Permintaan lama tidak boleh mengubah status permintaan yang lebih baru.
        if (permintaanAktif === permintaan) {
            hasilMahasiswa.setAttribute('aria-busy', 'false');
            permintaanAktif = null;
        }
    }
}

function isiFormEdit(tombol) {
    const formEdit = document.getElementById('formEditMahasiswa');
    formEdit.reset();
    formEdit.elements.nim.value = tombol.dataset.nim;
    formEdit.elements.nama_mhsw.value = tombol.dataset.nama;
    formEdit.elements.nilai_akhir.value = tombol.dataset.nilai;
    formEdit.elements.token.value = tombol.dataset.token;
    document.getElementById('pesanEditMahasiswa').classList.add('d-none');
}

// Debounce: tunggu pengguna berhenti mengetik sebentar sebelum meminta data.
kolomPencarian.addEventListener('input', () => {
    clearTimeout(timerPencarian);
    if (permintaanAktif !== null) {
        permintaanAktif.abort();
    }
    timerPencarian = setTimeout(() => muatDaftar(buatUrlDaftar()), jedaPencarian);
});

// Enter tetap dapat dipakai untuk langsung mencari tanpa menunggu jeda.
formFilter.addEventListener('submit', (event) => {
    event.preventDefault();
    muatDaftar(buatUrlDaftar());
});

document.getElementById('user-role-filter').addEventListener('change', () => {
    muatDaftar(buatUrlDaftar());
});

// Listener dipasang pada pembungkus, karena dropdown/tombol di dalamnya diganti.
hasilMahasiswa.addEventListener('change', (event) => {
    if (event.target.id === 'page-size') {
        muatDaftar(buatUrlDaftar());
    }
});

hasilMahasiswa.addEventListener('click', (event) => {
    const tautan = event.target.closest('a[data-halaman], a[data-urutan]');
    if (tautan) {
        // Ctrl/Cmd + klik tetap mengikuti perilaku asli tautan browser.
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
            return;
        }
        event.preventDefault();
        const halaman = tautan.dataset.halaman || 1;
        const urutan = tautan.dataset.urutan || formFilter.elements.sort.value;
        muatDaftar(buatUrlDaftar(halaman, urutan));
        return;
    }

    const tombolEdit = event.target.closest('.edit');
    if (tombolEdit) {
        isiFormEdit(tombolEdit);
    }
});

