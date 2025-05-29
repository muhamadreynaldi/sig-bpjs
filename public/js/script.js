window.addEventListener('DOMContentLoaded', event => {
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            // Toggle kelas pada #wrapper
            document.getElementById('wrapper').classList.toggle('toggled');
        });
    }
});

// Variabel global untuk peta di modal agar bisa dihancurkan dan dibuat ulang
let mapDetailModalInstance = null;

function showPenerimaDetailModal(penerimaId) {
    const modalElement = document.getElementById('penerimaDetailModal');
    const penerimaModal = new bootstrap.Modal(modalElement);

    // Reset konten modal sebelumnya
    document.getElementById('detailNama').textContent = 'Memuat...';
    document.getElementById('detailNik').textContent = '';
    // ... reset field lainnya ...
    if (mapDetailModalInstance) {
        mapDetailModalInstance.remove(); // Hapus instance peta lama
        mapDetailModalInstance = null;
    }
    document.getElementById('mapDetailModal').innerHTML = ''; // Bersihkan div peta


    fetch(`/api/penerima-detail/${penerimaId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Data penerima tidak ditemukan atau terjadi kesalahan.');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('penerimaDetailModalLabel').textContent = 'Detail Penerima: ' + data.nama;
            document.getElementById('detailNama').textContent = data.nama;
            document.getElementById('detailNik').textContent = data.nik;
            document.getElementById('detailAlamat').textContent = data.alamat || '-';
            document.getElementById('detailDusun').textContent = data.dusun;
            const detailStatusCell = document.getElementById('detailStatus');
            detailStatusCell.innerHTML = '';
            const statusBadge = document.createElement('span');
            const statusColorClass = getStatusColor(data.status); // Panggil fungsi di sini
            statusBadge.className = `badge ${statusColorClass}`; // `bg-warning text-dark` akan jadi `badge bg-warning text-dark`
            statusBadge.textContent = data.status;
            detailStatusCell.appendChild(statusBadge);
            document.getElementById('detailLat').textContent = data.lat;
            document.getElementById('detailLng').textContent = data.lng;
            document.getElementById('detailUpdatedAt').textContent = data.updated_at_formatted;

            if (data.lat && data.lng) {
                mapDetailModalInstance = L.map('mapDetailModal').setView([data.lat, data.lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(mapDetailModalInstance);
                L.marker([data.lat, data.lng]).addTo(mapDetailModalInstance)
                    .bindPopup(`<b>${data.nama}</b>`).openPopup();

                // Perlu me-resize peta setelah modal ditampilkan agar tile tidak rusak
                modalElement.addEventListener('shown.bs.modal', function () {
                    if (mapDetailModalInstance) {
                        mapDetailModalInstance.invalidateSize();
                    }
                }, { once: true });

                // Di dalam showPenerimaDetailModal, setelah peta diinisialisasi
const detailMarker = L.marker([data.lat, data.lng], {
    icon: L.icon({ // Explicitly define icon similar to default
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41], // Titik di mana ikon "menancap" di peta
        popupAnchor: [1, -34]  // Posisi popup relatif ke iconAnchor
    })
}).addTo(mapDetailModalInstance);
detailMarker.bindPopup(`<b>${data.nama}</b>`);

modalElement.addEventListener('shown.bs.modal', function () {
    if (mapDetailModalInstance) {
        mapDetailModalInstance.invalidateSize();
        // Setelah invalidateSize, coba buka popup di sini agar posisinya dihitung ulang
        detailMarker.openPopup();
        // Atau jika sudah terbuka, coba pan sedikit agar Leaflet refresh
        // mapDetailModalInstance.panBy([1,1]); mapDetailModalInstance.panBy([-1,-1]);
    }
}, { once: true });
            } else {
                document.getElementById('mapDetailModal').innerHTML = '<p class="text-center text-muted">Koordinat tidak tersedia.</p>';
            }
            penerimaModal.show();
        })
        .catch(error => {
            console.error('Error fetching penerima detail:', error);
            alert(error.message || 'Gagal memuat detail penerima.');
        });
}

function getStatusColor(status) { // Pastikan nama fungsi ini yang dipanggil
    switch (String(status).toLowerCase()) {
        case 'aktif': return 'bg-success'; // Hanya kembalikan kelas background
        case 'nonaktif': return 'bg-warning text-dark'; // Kelas background dan teks jika perlu
        case 'meninggal': return 'bg-dark';
        default: return 'bg-secondary';
    }
}