// Tambah dan edit memakai alur yang sama: kirim form, lalu baca hasil PHP.
function simpanFormMahasiswa(form, kotakPesan) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const tombolSimpan = form.querySelector('button[type="submit"]');
        tombolSimpan.disabled = true;
        kotakPesan.classList.add('d-none');

        try {
            // FormData membawa input teks, token, dan file foto sekaligus.
            const respons = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
            });

            // Tambah data berhasil: PHP mengarahkan kembali ke users.php.
            if (respons.ok && respons.redirected) {
                window.location.href = respons.url;
                return;
            }

            // Edit dari modal berhasil: PHP mengirim JSON {"success": true}.
            const jenisKonten = respons.headers.get('Content-Type') || '';
            if (respons.ok && jenisKonten.includes('application/json')) {
                const hasil = await respons.json();
                if (hasil.success) {
                    window.location.reload();
                    return;
                }
            }

            // Pesan kesalahan dari PHP ditampilkan sebagai teks biasa.
            const htmlPesan = await respons.text();
            const dokumenPesan = new DOMParser().parseFromString(htmlPesan, 'text/html');
            kotakPesan.textContent = dokumenPesan.body.textContent;
            kotakPesan.classList.remove('d-none');
        } catch (error) {
            kotakPesan.textContent = 'Data gagal dikirim. Silakan coba kembali.';
            kotakPesan.classList.remove('d-none');
        } finally {
            tombolSimpan.disabled = false;
        }
    });
}

const formTambah = document.getElementById('formTambahMahasiswa');
const pesanTambah = document.getElementById('pesanTambahMahasiswa');
simpanFormMahasiswa(formTambah, pesanTambah);

const formEdit = document.getElementById('formEditMahasiswa');
const pesanEdit = document.getElementById('pesanEditMahasiswa');
simpanFormMahasiswa(formEdit, pesanEdit);
