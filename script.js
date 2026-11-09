// Fungsi untuk menampilkan/menyembunyikan modal
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

// Close modal ketika klik di luar modal
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
}

// Konfirmasi hapus data
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        window.location.href = 'survey.php?action=delete&id=' + id;
    }
}

// Fungsi untuk search real-time
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toUpperCase();
    const table = document.querySelector('table tbody');
    const tr = table.getElementsByTagName('tr');

    for (let i = 0; i < tr.length; i++) {
        let found = false;
        const td = tr[i].getElementsByTagName('td');
        
        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                const txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        if (found) {
            tr[i].style.display = '';
        } else {
            tr[i].style.display = 'none';
        }
    }
}

// Validasi form sebelum submit
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            input.style.borderColor = '#ddd';
        }
    });

    if (!isValid) {
        alert('Mohon lengkapi semua field yang wajib diisi!');
    }

    return isValid;
}

// Format angka ke rupiah
function formatRupiah(angka, prefix = 'Rp ') {
    const number_string = angka.replace(/[^,\d]/g, '').toString();
    const split = number_string.split(',');
    const sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        const separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix + rupiah;
}

// Auto format input rupiah
document.addEventListener('DOMContentLoaded', function() {
    const rupiahInputs = document.querySelectorAll('.rupiah-input');
    
    rupiahInputs.forEach(input => {
        input.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value, '');
        });
    });
});

// Print function
function printTable() {
    window.print();
}

// Countdown auto logout (optional)
let idleTime = 0;
const idleInterval = setInterval(timerIncrement, 60000); // 1 menit

function timerIncrement() {
    idleTime++;
    if (idleTime > 30) { // 30 menit
        alert('Sesi Anda telah berakhir. Silakan login kembali.');
        window.location.href = 'logout.php';
    }
}

// Reset idle time on activity
document.addEventListener('mousemove', function() {
    idleTime = 0;
});
document.addEventListener('keypress', function() {
    idleTime = 0;
});

// Show loading overlay
function showLoading() {
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:9999;display:flex;justify-content:center;align-items:center;';
    overlay.innerHTML = '<div style="color:white;font-size:20px;">Loading...</div>';
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}