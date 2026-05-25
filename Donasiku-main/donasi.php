<?php include 'component/header.php'; ?>

<style>
    .hero-catalog {
        background:
            linear-gradient(rgba(5, 150, 105, 0.7), rgba(5, 150, 105, 0.85)),
            url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=1200&q=80');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        padding: 80px 0;
        color: white;
        text-align: center;
    }

    .nav-pills .nav-link {
        color: #64748b;
        font-weight: 600;
        border-radius: 50px;
        padding: 8px 20px;
        margin: 5px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        cursor: pointer;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    }

    .nav-pills .nav-link:hover {
        color: #059669;
        border-color: #059669;
    }

    .nav-pills .nav-link.active {
        background-color: #059669;
        color: white;
        border-color: #059669;
        box-shadow: 0 4px 10px rgba(5, 150, 105, 0.2);
    }

    .program-card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: none;
    }

    .program-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .program-image {
        height: 200px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .progress-container {
        padding: 15px;
    }

    .progress-bar {
        background-color: #059669;
    }

    .category-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .category-jariyah {
        background: #e0f2fe;
        color: #0369a1;
    }

    .category-yatim {
        background: #fef3c7;
        color: #b45309;
    }

    .category-pangan {
        background: #dcfce7;
        color: #15803d;
    }

    .category-darurat {
        background: #fee2e2;
        color: #b91c1c;
    }

    .modal-dialog {
        max-width: 700px;
    }

    @keyframes fadeInScale {
        0% {
            opacity: 0;
            transform: scale(0.95);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    .program-item {
        animation: fadeInScale 0.4s ease-in-out;
    }

    .loading-spinner {
        text-align: center;
        padding: 40px;
    }
</style>

<header class="hero-catalog mb-4">
    <div class="container" data-aos="fade-down">
        <h2 class="fw-bold mb-2">Pilih Program Kebaikan</h2>
        <p class="lead mb-0">Temukan kampanye donasi yang ingin Anda bantu hari ini.</p>
    </div>
</header>

<div class="container mb-5">
    <div class="d-flex justify-content-center flex-wrap mb-5" data-aos="fade-up" id="filter-container">
        <ul class="nav nav-pills justify-content-center">
            <li class="nav-item">
                <button class="nav-link active filter-btn" data-filter="all">Semua Program</button>
            </li>
            <li class="nav-item">
                <button class="nav-link filter-btn" data-filter="jariyah">Sedekah Jariyah</button>
            </li>
            <li class="nav-item">
                <button class="nav-link filter-btn" data-filter="yatim">Anak Yatim</button>
            </li>
            <li class="nav-item">
                <button class="nav-link filter-btn" data-filter="pangan">Pangan</button>
            </li>
            <li class="nav-item">
                <button class="nav-link filter-btn" data-filter="darurat">Darurat</button>
            </li>
        </ul>
    </div>

    <div id="loading" class="loading-spinner">
        <div class="spinner-border text-success me-2" role="status"></div>
        <span>Memuat program donasi...</span>
    </div>

    <div class="row" id="programs-container">
        <!-- Program cards akan di-load via JavaScript -->
    </div>

    <div id="empty-state" class="text-center py-5" style="display: none;">
        <h5>Belum ada program</h5>
        <p class="text-muted">Program dengan kategori ini sedang tidak tersedia</p>
    </div>
</div>

<div class="modal fade" id="donasiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Berikan Donasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="program-detail-modal" class="mb-4"></div>

                <form id="donasiForm">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah Donasi</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="amountInput" class="form-control" placeholder="0" inputmode="numeric" autocomplete="off" required>
                        </div>
                        <small class="text-muted">Minimal Rp 10.000</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Metode Pembayaran</label>
                        <select id="paymentMethod" class="form-select" required>
                            <option value="">Pilih metode pembayaran</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="QRIS">QRIS</option>
                            <option value="E-Wallet">E-Wallet</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pesan (Opsional)</label>
                        <textarea id="messageInput" class="form-control" rows="3" placeholder="Tulis pesan doa atau harapan..."></textarea>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <small>Donasi Anda akan diverifikasi oleh tim kami. Pastikan metode pembayaran sudah selesai sebelum ditutup.</small>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold py-2">Lanjutkan Pembayaran</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let allPrograms = [];
    let selectedProgramId = null;

    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(value);
    }

    function formatNumberInput(value) {
        const digitsOnly = String(value).replace(/[^0-9]/g, '');

        if (!digitsOnly) {
            return '';
        }

        return new Intl.NumberFormat('id-ID').format(Number(digitsOnly));
    }

    function parseRupiahInput(value) {
        return parseInt(String(value).replace(/[^0-9]/g, ''), 10) || 0;
    }

    function getCategoryClass(category) {
        return 'category-' + category;
    }

    function getCategoryLabel(category) {
        const labels = {
            jariyah: 'Sedekah Jariyah',
            yatim: 'Anak Yatim',
            pangan: 'Pangan',
            darurat: 'Darurat'
        };

        return labels[category] || category;
    }

    function getProgramImage(program) {
        if (program.image_url) {
            return program.image_url;
        }

        const fallbackImages = {
            jariyah: 'https://images.unsplash.com/photo-1564769662533-4f00a87b4056?auto=format&fit=crop&w=800&q=80',
            yatim: 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=800&q=80',
            pangan: 'https://images.unsplash.com/photo-1593113630400-ea4288922497?auto=format&fit=crop&w=800&q=80',
            darurat: 'https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=800&q=80'
        };

        return fallbackImages[program.category] || fallbackImages.pangan;
    }

    function createProgramCard(program) {
        const description = program.description || 'Program donasi untuk berbagi kebaikan';

        return (
            '<div class="col-md-6 col-lg-4 mb-4 program-item" data-category="' + program.category + '">' +
                '<div class="card program-card h-100">' +
                    '<img src="' + getProgramImage(program) + '" class="program-image" alt="' + program.title + '" onerror="this.onerror=null; this.src=\'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=800&q=80\';">' +
                    '<div class="card-body d-flex flex-column">' +
                        '<span class="category-badge ' + getCategoryClass(program.category) + '">' +
                            getCategoryLabel(program.category) +
                        '</span>' +
                        '<h5 class="card-title fw-bold">' + program.title + '</h5>' +
                        '<p class="card-text text-muted small">' + description + '</p>' +
                        '<div class="progress-container mt-auto">' +
                            '<div class="d-flex justify-content-between mb-2">' +
                                '<small class="text-muted">Terkumpul</small>' +
                                '<small class="fw-bold text-success">' + program.percentage + '%</small>' +
                            '</div>' +
                            '<div class="progress" style="height: 8px;">' +
                                '<div class="progress-bar" style="width: ' + program.percentage + '%"></div>' +
                            '</div>' +
                            '<div class="d-flex justify-content-between mt-2">' +
                                '<small>' + formatCurrency(program.collected_amount) + '</small>' +
                                '<small class="text-muted">dari ' + formatCurrency(program.target_amount) + '</small>' +
                            '</div>' +
                        '</div>' +
                        '<button class="btn btn-success w-100 mt-3 fw-bold" onclick="openDonasiModal(' + program.id + ')">' +
                            'Berikan Donasi' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );
    }

    function displayPrograms(programs) {
        const container = document.getElementById('programs-container');
        const emptyState = document.getElementById('empty-state');

        if (programs.length === 0) {
            container.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        container.innerHTML = programs.map(createProgramCard).join('');
    }

    function showError(message) {
        document.getElementById('loading').style.display = 'none';
        Swal.fire({ icon: 'error', title: 'Error', text: message });
    }

    async function loadPrograms() {
        try {
            const response = await fetch('./api/programs.php?action=list');
            const result = await response.json();

            if (result.success) {
                allPrograms = result.data;
                displayPrograms(allPrograms);
                document.getElementById('loading').style.display = 'none';
            } else {
                showError('Gagal memuat program');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Terjadi kesalahan saat memuat program');
        }
    }

    async function openDonasiModal(programId) {
        try {
            const response = await fetch('./api/check-session.php');
            const result = await response.json();

            if (!result.logged_in) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Silakan Login',
                    text: 'Anda harus login untuk melakukan donasi',
                    confirmButtonText: 'Ke Login'
                }).then(function () {
                    window.location.href = 'login.php?redirect=donasi.php';
                });
                return;
            }

            selectedProgramId = programId;

            const programResponse = await fetch('./api/programs.php?action=detail&id=' + programId);
            const programResult = await programResponse.json();

            if (programResult.success) {
                const program = programResult.data;
                const detailHtml =
                    '<div class="text-center mb-3">' +
                        '<h6 class="fw-bold">' + program.title + '</h6>' +
                        '<small class="text-muted d-block">Target: ' + formatCurrency(program.target_amount) + '</small>' +
                        '<small class="text-success fw-bold d-block">Terkumpul: ' + formatCurrency(program.collected_amount) + ' (' + program.percentage + '%)</small>' +
                        '<small class="text-muted d-block mt-2">' + program.donor_count + ' pendonasi</small>' +
                    '</div>';

                document.getElementById('program-detail-modal').innerHTML = detailHtml;
            }

            const modal = new bootstrap.Modal(document.getElementById('donasiModal'));
            modal.show();
        } catch (error) {
            console.error('Error:', error);
            showError('Terjadi kesalahan saat membuka form donasi');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.filter-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const currentFilter = this.dataset.filter;

                document.querySelectorAll('.filter-btn').forEach(function (button) {
                    button.classList.remove('active');
                });

                this.classList.add('active');

                if (currentFilter === 'all') {
                    displayPrograms(allPrograms);
                    return;
                }

                displayPrograms(allPrograms.filter(function (program) {
                    return program.category === currentFilter;
                }));
            });
        });

        document.getElementById('amountInput').addEventListener('input', function () {
            this.value = formatNumberInput(this.value);
        });

        document.getElementById('donasiForm').addEventListener('submit', async function (event) {
            event.preventDefault();

            const amount = parseRupiahInput(document.getElementById('amountInput').value);
            const paymentMethod = document.getElementById('paymentMethod').value;
            const message = document.getElementById('messageInput').value;

            if (amount < 10000) {
                Swal.fire({ icon: 'warning', title: 'Jumlah Tidak Valid', text: 'Minimal donasi Rp 10.000' });
                return;
            }

            Swal.fire({
                title: 'Memproses...',
                icon: 'info',
                allowOutsideClick: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch('./api/create-donasi.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        program_id: selectedProgramId,
                        amount: amount,
                        payment_method: paymentMethod,
                        message: message
                    })
                });

                const responseText = await response.text();
                let result;

                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    throw new Error(responseText || 'Response server tidak valid');
                }

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Donasi Berhasil Dibuat!',
                        text: 'Kode Transaksi: ' + result.data.trx_code + '\nSilakan lakukan pembayaran',
                        confirmButtonText: 'Ke Dashboard'
                    }).then(function () {
                        window.location.href = 'user-dashboard.php';
                    });
                    return;
                }

                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message });
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({ icon: 'error', title: 'Kesalahan', text: 'Terjadi kesalahan jaringan' });
            }
        });

        loadPrograms();
    });
</script>

<?php include 'component/footer.php'; ?>
