window.addEventListener('DOMContentLoaded', event => {
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.getElementById('wrapper').classList.toggle('toggled');
        });
    }
});

let mapDetailModalInstance = null;

function showPenerimaDetailModal(penerimaId) {
    const modalElement = document.getElementById('penerimaDetailModal');
    const penerimaModal = new bootstrap.Modal(modalElement);

    document.getElementById('detailNama').textContent = 'Memuat...';
    document.getElementById('detailNik').textContent = '';
    if (mapDetailModalInstance) {
        mapDetailModalInstance.remove();
        mapDetailModalInstance = null;
    }
    document.getElementById('mapDetailModal').innerHTML = '';


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
            const statusColorClass = getStatusColor(data.status);
            statusBadge.className = `badge ${statusColorClass}`;
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

                modalElement.addEventListener('shown.bs.modal', function () {
                    if (mapDetailModalInstance) {
                        mapDetailModalInstance.invalidateSize();
                    }
                }, { once: true });

const detailMarker = L.marker([data.lat, data.lng], {
    icon: L.icon({
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    })
}).addTo(mapDetailModalInstance);
detailMarker.bindPopup(`<b>${data.nama}</b>`);

modalElement.addEventListener('shown.bs.modal', function () {
    if (mapDetailModalInstance) {
        mapDetailModalInstance.invalidateSize();
        detailMarker.openPopup();
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

function getStatusColor(status) {
    switch (String(status).toLowerCase()) {
        case 'aktif': return 'bg-success';
        case 'nonaktif': return 'bg-warning text-dark';
        case 'meninggal': return 'bg-dark';
        default: return 'bg-secondary';
    }
}