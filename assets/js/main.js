document.addEventListener('DOMContentLoaded', function() {
    const editButton = document.querySelector('.edit-celengan-btn');
    const modal = document.getElementById('editModal');
    const closeButton = document.querySelector('.close-button');

    if (editButton && modal && closeButton) {
        // Tampilkan modal ketika tombol edit diklik
        editButton.onclick = function(e) {
            e.preventDefault(); // Mencegah navigasi
            modal.style.display = 'block';
        }

        // Sembunyikan modal ketika tombol silang diklik
        closeButton.onclick = function() {
            modal.style.display = 'none';
        }

        // Sembunyikan modal ketika user mengklik di luar modal
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    }
});