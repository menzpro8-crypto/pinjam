// Data State
let alatData = [];
let anggotaData = [];
let peminjamanData = [];
let activeTab = 'dashboard';

// API Configuration
const API_BASE = 'http://localhost/pinjam/api.php';

// DOM Elements
const menuItems = document.querySelectorAll('.menu-item');
const tabContents = document.querySelectorAll('.tab-content');
const pageTitle = document.querySelector('.page-title');

// Modal Elements
const tambahAlatModal = document.getElementById('tambahAlatModal');
const tambahAnggotaModal = document.getElementById('tambahAnggotaModal');
const peminjamanModal = document.getElementById('peminjamanModal');
const closeModalButtons = document.querySelectorAll('.close-modal, .btn#batalAlatBtn, .btn#batalAnggotaBtn, .btn#batalPeminjamanBtn');

// Initialize the application
document.addEventListener('DOMContentLoaded', async function() {
    await initData();
    checkNotifications(); // Update notification indicator on load
    setupEventListeners();
    renderDashboard();

    // Trigger opening animations
    setTimeout(() => {
        document.querySelector('.sidebar').classList.add('animate-in');
        document.querySelector('.main-content').classList.add('animate-in');

        // Animate stat cards
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.classList.add('animate-in');
        });
    }, 100);
});

// Initialize with API data or PHP data
async function initData() {
    // Check if we have initial data from PHP
    if (typeof initialData !== 'undefined') {
        // Use PHP-provided data
        alatData = initialData.alat.data || [];
        anggotaData = initialData.anggota.data || [];
        peminjamanData = initialData.peminjaman.data || [];
        console.log('Data loaded from PHP');
        return;
    }

    // Fallback to API loading
    try {
        // Load data from API with cache busting
        const [alatResponse, anggotaResponse, peminjamanResponse] = await Promise.all([
            fetch(`${API_BASE}/alat?t=${Date.now()}`),
            fetch(`${API_BASE}/anggota?t=${Date.now()}`),
            fetch(`${API_BASE}/peminjaman?t=${Date.now()}`)
        ]);

        if (alatResponse.ok) alatData = await alatResponse.json();
        if (anggotaResponse.ok) anggotaData = await anggotaResponse.json();
        if (peminjamanResponse.ok) peminjamanData = await peminjamanResponse.json();

        console.log('Data loaded from API');
    } catch (error) {
        console.error('Error loading data from API:', error);
        // Fallback to sample data if API fails
        loadSampleData();
    }
}

// Fallback sample data
function loadSampleData() {
    alatData = [
        {
            id: 1,
            nama: 'Laptop Dell XPS 15',
            deskripsi: 'Laptop untuk desain grafis dan programming dengan spesifikasi tinggi',
            kategori: 'Elektronik',
            stock: 3,
            status: 'Tersedia',
            foto: 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500&h=300&fit=crop'
        },
        {
            id: 2,
            nama: 'Proyektor Epson',
            deskripsi: 'Proyektor untuk presentasi dengan resolusi HD',
            kategori: 'Elektronik',
            stock: 3,
            status: 'Tersedia',
            foto: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&h=300&fit=crop'
        },
        {
            id: 3,
            nama: 'Kamera Canon EOS 80D',
            deskripsi: 'Kamera DSLR untuk fotografi profesional',
            kategori: 'Fotografi',
            stock: 3,
            status: 'Tersedia',
            foto: 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=500&h=300&fit=crop'
        },
        {
            id: 4,
            nama: 'Mikrofon Rode NT1',
            deskripsi: 'Mikrofon kondenser untuk rekaman studio',
            kategori: 'Audio',
            stock: 3,
            status: 'Tersedia',
            foto: 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?w=500&h=300&fit=crop'
        },
        {
            id: 5,
            nama: 'Tablet iPad Pro 12.9"',
            deskripsi: 'Tablet premium untuk kreativitas dan produktivitas',
            kategori: 'Elektronik',
            stock: 2,
            status: 'Tersedia',
            foto: 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=500&h=300&fit=crop'
        },
        {
            id: 6,
            nama: 'Speaker Bluetooth JBL',
            deskripsi: 'Speaker portabel dengan kualitas suara tinggi',
            kategori: 'Audio',
            stock: 5,
            status: 'Tersedia',
            foto: 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500&h=300&fit=crop'
        },
        {
            id: 7,
            nama: 'Drone DJI Mini 3',
            deskripsi: 'Drone ringan untuk fotografi udara dan videografi',
            kategori: 'Fotografi',
            stock: 1,
            status: 'Tersedia',
            foto: 'https://images.unsplash.com/photo-1473968512647-3e447244af8f?w=500&h=300&fit=crop'
        },
        {
            id: 8,
            nama: 'Monitor LG 27"',
            deskripsi: 'Monitor 4K untuk editing video dan desain',
            kategori: 'Elektronik',
            stock: 4,
            status: 'Tersedia',
            foto: 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&h=300&fit=crop'
        },
        {
            id: 9,
            nama: 'Headphone Sony WH-1000XM4',
            deskripsi: 'Headphone noise-cancelling untuk musik dan panggilan',
            kategori: 'Audio',
            stock: 0,
            status: 'Tersedia',
            foto: 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=500&h=300&fit=crop'
        }
    ];

    anggotaData = [
        {
            id: 1,
            nama: 'Ahmad Rahman',
            kelas: 'XII RPL 1',
            kontak: '081234567890',
            status: 'Aktif'
        },
        {
            id: 2,
            nama: 'Siti Nurhaliza',
            kelas: 'XII RPL 2',
            kontak: '081234567891',
            status: 'Aktif'
        },
        {
            id: 3,
            nama: 'Budi Santoso',
            kelas: 'XII TKJ 1',
            kontak: '081234567892',
            status: 'Aktif'
        },
        {
            id: 4,
            nama: 'Maya Sari',
            kelas: 'XII MM 1',
            kontak: '081234567893',
            status: 'Aktif'
        },
        {
            id: 5,
            nama: 'Rizki Pratama',
            kelas: 'XII RPL 1',
            kontak: '081234567894',
            status: 'Aktif'
        },
        {
            id: 6,
            nama: 'Dewi Lestari',
            kelas: 'XII TKJ 2',
            kontak: '081234567895',
            status: 'Aktif'
        },
        {
            id: 7,
            nama: 'Fajar Nugroho',
            kelas: 'XII MM 2',
            kontak: '081234567896',
            status: 'Aktif'
        },
        {
            id: 8,
            nama: 'Intan Permata',
            kelas: 'XII RPL 2',
            kontak: '081234567897',
            status: 'Aktif'
        },
        {
            id: 9,
            nama: 'Gilang Ramadhan',
            kelas: 'XII TKJ 1',
            kontak: '081234567898',
            status: 'Aktif'
        },
        {
            id: 10,
            nama: 'Nadia Putri',
            kelas: 'XII MM 1',
            kontak: '081234567899',
            status: 'Aktif'
        }
    ];

    peminjamanData = [
        {
            id: 1,
            alat_id: 2,
            nama_alat: 'Proyektor Epson',
            peminjam: 'Budi Santoso',
            tanggal_mulai: '2023-10-20',
            tanggal_selesai: '2023-10-25',
            status: 'Aktif'
        },
        {
            id: 2,
            alat_id: 3,
            nama_alat: 'Kamera Canon EOS 80D',
            peminjam: 'Siti Rahayu',
            tanggal_mulai: '2023-10-15',
            tanggal_selesai: '2023-10-18',
            status: 'Selesai'
        }
    ];
}

// Setup event listeners
function setupEventListeners() {
    // Tab navigation
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const tab = this.getAttribute('data-tab');
            switchTab(tab);
        });
    });

    // Menu toggle for mobile
    document.querySelector('.menu-toggle').addEventListener('click', toggleSidebar);

    // Modal buttons
    document.getElementById('tambahAlatBtn').addEventListener('click', () => {
        showModal(tambahAlatModal);
    });

    document.getElementById('tambahAnggotaBtn').addEventListener('click', () => {
        showModal(tambahAnggotaModal);
    });

    // Notification button
    document.querySelector('.notification-btn').addEventListener('click', showNotifications);

    // Close modal buttons
    closeModalButtons.forEach(button => {
        button.addEventListener('click', closeAllModals);
    });

    // Form submissions
    document.getElementById('simpanAlatBtn').addEventListener('click', tambahAlat);
    document.getElementById('simpanAnggotaBtn').addEventListener('click', tambahAnggota);
    document.getElementById('simpanPeminjamanBtn').addEventListener('click', catatPeminjaman);

    // Filter changes
    document.getElementById('filterPeriode').addEventListener('change', renderLaporan);
    document.getElementById('filterBulan').addEventListener('change', renderLaporan);
    document.getElementById('filterTahun').addEventListener('change', renderLaporan);

    // Kelas dropdown change event
    document.getElementById('kelasPeminjam').addEventListener('change', function() {
        const selectedKelas = this.value;
        updateAnggotaDropdown(selectedKelas);
    });

    // Logout button
    document.getElementById('btnLogout').addEventListener('click', function() {
        showCustomConfirm('warning', 'Logout', 'Apakah Anda yakin ingin keluar?', function() {
            window.location.href = 'logout.php';
        });
    });
}

// Switch between tabs
function switchTab(tabName) {
    // Close sidebar on mobile when switching tabs
    if (window.innerWidth <= 768 && document.querySelector('.sidebar').classList.contains('active')) {
        toggleSidebar();
    }

    // Update active menu item
    menuItems.forEach(item => {
        if (item.getAttribute('data-tab') === tabName) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    // Update active tab content
    tabContents.forEach(tab => {
        if (tab.id === tabName) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    // Update page title
    const titles = {
        dashboard: 'Dashboard',
        dataAlat: 'Data Alat',
        dataAnggota: 'Data Anggota',
        peminjaman: 'Peminjaman Alat',
        laporan: 'Laporan Peminjaman'
    };
    pageTitle.textContent = titles[tabName];

    // Render appropriate content
    activeTab = tabName;
    switch(tabName) {
        case 'dashboard':
            renderDashboard();
            break;
        case 'dataAlat':
            renderDataAlat();
            break;
        case 'dataAnggota':
            renderDataAnggota();
            break;
        case 'peminjaman':
            renderPeminjaman();
            break;
        case 'laporan':
            renderLaporan();
            break;
    }
}

// Show modal
function showModal(modal) {
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('fade-in');
    }, 10);
}

// Close all modals
function closeAllModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    });
}

// Render dashboard
// Refresh dashboard statistics from server
async function refreshDashboardStats() {
    try {
        const response = await fetch(`${API_BASE}/dashboard`);
        const stats = await response.json();

        document.getElementById('totalAlat').textContent = stats.total_alat;
        document.getElementById('alatTersedia').textContent = stats.total_stock;
        document.getElementById('alatDipinjam').textContent = stats.peminjaman_aktif;
        document.getElementById('peminjamanAktif').textContent = stats.peminjaman_aktif;
        document.getElementById('peminjamanSelesai').textContent = stats.peminjaman_selesai;

        // Render peminjaman terbaru table
        const tableBody = document.getElementById('peminjamanTerbaruTable');
        tableBody.innerHTML = '';

        stats.peminjaman_terbaru.forEach(p => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${p.nama_alat}</td>
                <td>${p.peminjam}</td>
                <td>${formatDate(p.tanggal_mulai)}</td>
                <td><span class="status-badge ${p.status === 'Aktif' ? 'status-borrowed' : 'status-available'}">${p.status}</span></td>
            `;
            tableBody.appendChild(row);
        });

        console.log('Dashboard stats refreshed');
    } catch (error) {
        console.error('Error refreshing dashboard stats:', error);
    }
}

async function renderDashboard() {
    if (typeof initialData !== 'undefined' && initialData.dashboard) {
        // Use PHP-provided data
        const stats = initialData.dashboard;

        document.getElementById('totalAlat').textContent = stats.total_alat;
        document.getElementById('alatTersedia').textContent = stats.total_stock;
        document.getElementById('alatDipinjam').textContent = stats.peminjaman_aktif;
        document.getElementById('peminjamanAktif').textContent = stats.peminjaman_aktif;
        document.getElementById('peminjamanSelesai').textContent = stats.peminjaman_selesai;

        // Render peminjaman terbaru table
        const tableBody = document.getElementById('peminjamanTerbaruTable');
        tableBody.innerHTML = '';

        stats.peminjaman_terbaru.forEach(p => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${p.nama_alat}</td>
                <td>${p.peminjam}</td>
                <td>${formatDate(p.tanggal_mulai)}</td>
                <td><span class="status-badge ${p.status === 'Aktif' ? 'status-borrowed' : 'status-available'}">${p.status}</span></td>
            `;
            tableBody.appendChild(row);
        });
    } else {
        // Fallback to API or local calculation
        try {
            const response = await fetch(`${API_BASE}/dashboard`);
            const stats = await response.json();

            document.getElementById('totalAlat').textContent = stats.total_alat;
            document.getElementById('alatTersedia').textContent = stats.total_stock;
            document.getElementById('alatDipinjam').textContent = stats.peminjaman_aktif;
            document.getElementById('peminjamanAktif').textContent = stats.peminjaman_aktif;
            document.getElementById('peminjamanSelesai').textContent = stats.peminjaman_selesai;

            // Render peminjaman terbaru table
            const tableBody = document.getElementById('peminjamanTerbaruTable');
            tableBody.innerHTML = '';

            stats.peminjaman_terbaru.forEach(p => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${p.nama_alat}</td>
                    <td>${p.peminjam}</td>
                    <td>${formatDate(p.tanggal_mulai)}</td>
                    <td><span class="status-badge ${p.status === 'Aktif' ? 'status-borrowed' : 'status-available'}">${p.status}</span></td>
                `;
                tableBody.appendChild(row);
            });
        } catch (error) {
            console.error('Error loading dashboard:', error);
            // Fallback to local calculation if API fails
            const totalAlat = alatData.length;
            const totalStock = alatData.reduce((sum, a) => sum + a.stock, 0);
            const peminjamanAktif = peminjamanData.filter(p => p.status === 'Aktif').length;

            document.getElementById('totalAlat').textContent = totalAlat;
            document.getElementById('alatTersedia').textContent = totalStock;
            document.getElementById('alatDipinjam').textContent = peminjamanAktif;
            document.getElementById('peminjamanAktif').textContent = peminjamanAktif;

            const tableBody = document.getElementById('peminjamanTerbaruTable');
            tableBody.innerHTML = '';

            const recentPeminjaman = peminjamanData.slice(0, 5);
            recentPeminjaman.forEach(p => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${p.nama_alat}</td>
                    <td>${p.peminjam}</td>
                    <td>${formatDate(p.tanggal_mulai)}</td>
                    <td><span class="status-badge ${p.status === 'Aktif' ? 'status-borrowed' : 'status-available'}">${p.status}</span></td>
                `;
                tableBody.appendChild(row);
            });
        }
    }
}

// Render data alat - Dynamically generate cards based on alatData
async function renderDataAlat() {
    const alatGrid = document.querySelector('.alat-grid');
    alatGrid.innerHTML = '';

    try {
        const response = await fetch(`${API_BASE}/alat`);
        const alatList = await response.json();

        alatList.forEach(alat => {
            const card = document.createElement('div');
            card.className = 'alat-card';
            card.setAttribute('data-id', alat.id);

            const stockClass = alat.stock === 0 ? 'out-of-stock' : (alat.stock <= 2 ? 'low-stock' : '');
            const buttonDisabled = alat.stock === 0 ? 'disabled' : '';
            const buttonText = alat.stock === 0 ? 'Stock Habis' : 'Pinjam';
            const statusText = alat.stock === 0 ? 'Habis' : 'Tersedia';
            const statusClass = alat.stock === 0 ? 'status-borrowed' : 'status-available';

            card.innerHTML = `
                <div class="alat-image">
                    <img src="${alat.foto}" alt="${alat.nama}" onerror="this.src='https://images.unsplash.com/photo-1565688534245-05d6b5be184a?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'">
                </div>
                <div class="alat-info">
                    <h3 class="alat-title">${alat.nama}</h3>
                    <p class="alat-description">${alat.deskripsi}</p>
                    <div class="alat-meta">
                        <span class="kategori">${alat.kategori}</span>
                        <span class="stock-info ${stockClass}" data-id="${alat.id}">Stock: ${alat.stock}</span>
                        <div class="delete-menu">
                            <button class="delete-btn" data-id="${alat.id}">⋮</button>
                            <div class="delete-options" data-id="${alat.id}">
                                <div class="delete-option" data-action="edit" data-id="${alat.id}"><i class="fas fa-edit"></i> Edit</div>
                                <div class="delete-option danger" data-action="delete" data-id="${alat.id}"><i class="fas fa-trash"></i> Hapus</div>
                            </div>
                        </div>
                    </div>
                    <div class="alat-actions">
                        <span class="status-badge ${statusClass}" data-id="${alat.id}">${statusText}</span>
                        <button class="btn btn-primary ${buttonDisabled}" data-id="${alat.id}"><i class="fas fa-handshake"></i> ${buttonText}</button>
                    </div>
                </div>
            `;

            alatGrid.appendChild(card);
        });
    } catch (error) {
        console.error('Error loading alat data:', error);
        // Fallback to local data if API fails
        alatData.forEach(alat => {
            const card = document.createElement('div');
            card.className = 'alat-card';
            card.setAttribute('data-id', alat.id);

            const stockClass = alat.stock === 0 ? 'out-of-stock' : (alat.stock <= 2 ? 'low-stock' : '');
            const buttonDisabled = alat.stock === 0 ? 'disabled' : '';
            const buttonText = alat.stock === 0 ? 'Stock Habis' : 'Pinjam';
            const statusText = alat.stock === 0 ? 'Habis' : 'Tersedia';
            const statusClass = alat.stock === 0 ? 'status-borrowed' : 'status-available';

            card.innerHTML = `
                <div class="alat-image">
                    <img src="${alat.foto}" alt="${alat.nama}" onerror="this.src='https://images.unsplash.com/photo-1565688534245-05d6b5be184a?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'">
                </div>
                <div class="alat-info">
                    <h3 class="alat-title">${alat.nama}</h3>
                    <p class="alat-description">${alat.deskripsi}</p>
                    <div class="alat-meta">
                        <span class="kategori">${alat.kategori}</span>
                        <span class="stock-info ${stockClass}" data-id="${alat.id}">Stock: ${alat.stock}</span>
                        <div class="delete-menu">
                            <button class="delete-btn" data-id="${alat.id}">⋮</button>
                            <div class="delete-options" data-id="${alat.id}">
                                <div class="delete-option" data-action="edit" data-id="${alat.id}"><i class="fas fa-edit"></i> Edit</div>
                                <div class="delete-option danger" data-action="delete" data-id="${alat.id}"><i class="fas fa-trash"></i> Hapus</div>
                            </div>
                        </div>
                    </div>
                    <div class="alat-actions">
                        <span class="status-badge ${statusClass}" data-id="${alat.id}">${statusText}</span>
                        <button class="btn btn-primary ${buttonDisabled}" data-id="${alat.id}"><i class="fas fa-handshake"></i> ${buttonText}</button>
                    </div>
                </div>
            `;

            alatGrid.appendChild(card);
        });
    }

    // Add event listeners to pinjam buttons
    document.querySelectorAll('.alat-card .btn').forEach(button => {
        button.addEventListener('click', function() {
            const alatId = parseInt(this.getAttribute('data-id'));
            const alat = alatData.find(a => a.id === alatId);

            if (!alat) {
                showCustomAlert('error', 'Error!', 'Alat tidak ditemukan!');
                return;
            }

            if (alat.stock <= 0) {
                showCustomAlert('warning', 'Stok Habis', 'Maaf, stok alat ini sudah habis dan tidak dapat dipinjam saat ini.');
                return;
            }

            updateAlatDropdown();
            updateKelasDropdown();
            updateAnggotaDropdown();
            document.getElementById('alatDipinjam').value = alatId;
            showModal(peminjamanModal);
        });
    });

    // Add event listeners to delete menu buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            // Close all other menus first
            document.querySelectorAll('.delete-options.show').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            // Toggle current menu
            menu.classList.toggle('show');
        });
    });

    // Add event listeners to delete options
    document.querySelectorAll('.delete-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const action = this.getAttribute('data-action');
            const alatId = parseInt(this.getAttribute('data-id'));

            if (action === 'delete') {
                hapusAlat(alatId);
            } else if (action === 'edit') {
                // For now, just show an alert. You can implement edit functionality later
                showCustomAlert('info', 'Info', 'Fitur edit akan segera hadir!');
            }

            // Close the menu
            this.closest('.delete-options').classList.remove('show');
        });
    });

    // Close menus when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.delete-options.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
}

// Helper function to render anggota rows
function renderAnggotaRows(anggotaList, tableBody) {
    anggotaList.forEach(anggota => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${anggota.nama}</td>
            <td>${anggota.kelas}</td>
            <td>${anggota.kontak}</td>
            <td><span class="status-badge ${anggota.status === 'Aktif' ? 'status-available' : 'status-borrowed'}">${anggota.status}</span></td>
            <td>
                <div class="delete-menu">
                    <button class="delete-btn" data-id="${anggota.id}">⋮</button>
                    <div class="delete-options" data-id="${anggota.id}">
                        <div class="delete-option" data-action="edit" data-id="${anggota.id}"><i class="fas fa-edit"></i> Edit</div>
                        <div class="delete-option danger" data-action="delete" data-id="${anggota.id}"><i class="fas fa-trash"></i> Hapus</div>
                    </div>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Render data anggota table
async function renderDataAnggota() {
    const tableBody = document.getElementById('anggotaTable');
    tableBody.innerHTML = '';

    try {
        const response = await fetch(`${API_BASE}/anggota`);
        const anggotaList = await response.json();
        renderAnggotaRows(anggotaList, tableBody);
    } catch (error) {
        console.error('Error loading anggota data:', error);
        // Fallback to local data if API fails
        renderAnggotaRows(anggotaData, tableBody);
    }

    // Add search functionality
    const searchInput = document.getElementById('searchAnggota');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            filterAnggotaTable(searchTerm);
        });
    }

    // Add event listeners to delete menu buttons
    document.querySelectorAll('#anggotaTable .delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            // Close all other menus first
            document.querySelectorAll('.delete-options.show').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            // Toggle current menu
            menu.classList.toggle('show');
        });
    });

    // Add event listeners to delete options
    document.querySelectorAll('#anggotaTable .delete-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const action = this.getAttribute('data-action');
            const anggotaId = parseInt(this.getAttribute('data-id'));

            if (action === 'delete') {
                hapusAnggota(anggotaId);
            } else if (action === 'edit') {
                // For now, just show an alert. You can implement edit functionality later
                showCustomAlert('info', 'Info', 'Fitur edit akan segera hadir!');
            }

            // Close the menu
            this.closest('.delete-options').classList.remove('show');
        });
    });

    // Close menus when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.delete-options.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
}

// Filter anggota table based on search input
function filterAnggotaTable(searchTerm) {
    const tableBody = document.getElementById('anggotaTable');
    const rows = tableBody.querySelectorAll('tr');

    if (!searchTerm) {
        // Show all rows if no search term
        rows.forEach(row => {
            row.style.display = '';
        });
        return;
    }

    rows.forEach(row => {
        const namaCell = row.cells[0]; // Nama column
        if (namaCell) {
            const nama = namaCell.textContent.toLowerCase();
            // Check if the name contains the search term (like Google search)
            const matches = nama.includes(searchTerm);
            row.style.display = matches ? '' : 'none';
        }
    });
}

// Render peminjaman table
async function renderPeminjaman() {
    const tableBody = document.getElementById('peminjamanTable');
    tableBody.innerHTML = '';

    try {
        const response = await fetch(`${API_BASE}/peminjaman`);
        const peminjamanList = await response.json();

        peminjamanList.forEach(p => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${p.nama_alat}</td>
                <td>${p.peminjam}</td>
                <td>${formatDate(p.tanggal_mulai)}</td>
                <td>${formatDate(p.tanggal_selesai)}</td>
                <td><span class="status-badge ${p.status === 'Aktif' ? 'status-borrowed' : 'status-available'}">${p.status}</span></td>
                <td>
                    ${p.status === 'Aktif' ?
                        `<button class="btn btn-success" data-id="${p.id}">Kembalikan</button>` :
                        '<span class="text-gray">-</span>'
                    }
                </td>
            `;
            tableBody.appendChild(row);
        });
    } catch (error) {
        console.error('Error loading peminjaman data:', error);
        // Fallback to local data if API fails
        peminjamanData.forEach(p => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${p.nama_alat}</td>
                <td>${p.peminjam}</td>
                <td>${formatDate(p.tanggal_mulai)}</td>
                <td>${formatDate(p.tanggal_selesai)}</td>
                <td><span class="status-badge ${p.status === 'Aktif' ? 'status-borrowed' : 'status-available'}">${p.status}</span></td>
                <td>
                    ${p.status === 'Aktif' ?
                        `<button class="btn btn-success" data-id="${p.id}">Kembalikan</button>` :
                        '<span class="text-gray">-</span>'
                    }
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    // Add event listeners to kembalikan buttons
    document.querySelectorAll('#peminjamanTable .btn-success').forEach(button => {
        button.addEventListener('click', function() {
            const peminjamanId = parseInt(this.getAttribute('data-id'));
            kembalikanAlat(peminjamanId);
        });
    });
}

// Render laporan
function renderLaporan() {
    const tableBody = document.getElementById('laporanTable');
    tableBody.innerHTML = '';

    const periode = document.getElementById('filterPeriode').value;
    const bulan = parseInt(document.getElementById('filterBulan').value);
    const tahun = parseInt(document.getElementById('filterTahun').value);

    const filteredData = peminjamanData.filter(p => {
        const tanggal = new Date(p.tanggalMulai);
        const peminjamanBulan = tanggal.getMonth() + 1;
        const peminjamanTahun = tanggal.getFullYear();

        if (periode === 'bulan') {
            return peminjamanBulan === bulan && peminjamanTahun === tahun;
        } else {
            // Simplified week filter (current week)
            const now = new Date();
            const startOfWeek = new Date(now.setDate(now.getDate() - now.getDay()));
            const endOfWeek = new Date(now.setDate(now.getDate() - now.getDay() + 6));
            return new Date(p.tanggalMulai) >= startOfWeek && new Date(p.tanggalMulai) <= endOfWeek;
        }
    });

    if (filteredData.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="5" class="text-center">Tidak ada data peminjaman untuk periode yang dipilih</td>`;
        tableBody.appendChild(row);
    } else {
        filteredData.forEach(p => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${p.namaAlat}</td>
                <td>${p.peminjam}</td>
                <td>${formatDate(p.tanggalMulai)}</td>
                <td>${formatDate(p.tanggalSelesai)}</td>
                <td><span class="status-badge ${p.status === 'Aktif' ? 'status-borrowed' : 'status-available'}">${p.status}</span></td>
            `;
            tableBody.appendChild(row);
        });
    }
}

// Update alat dropdown in peminjaman modal
function updateAlatDropdown() {
    const dropdown = document.getElementById('alatDipinjam');
    dropdown.innerHTML = '<option value="">Pilih Alat</option>';

    alatData
        .filter(alat => alat.stock > 0)
        .forEach(alat => {
            const option = document.createElement('option');
            option.value = alat.id;
            option.textContent = `${alat.nama} (Stock: ${alat.stock})`;
            dropdown.appendChild(option);
        });
}

// Update kelas dropdown in peminjaman modal
function updateKelasDropdown() {
    const dropdown = document.getElementById('kelasPeminjam');
    dropdown.innerHTML = '<option value="">Pilih Kelas</option>';

    // Get unique kelas from anggotaData
    const uniqueKelas = [...new Set(anggotaData
        .filter(anggota => anggota.status === 'Aktif')
        .map(anggota => anggota.kelas)
    )];

    uniqueKelas.forEach(kelas => {
        const option = document.createElement('option');
        option.value = kelas;
        option.textContent = kelas;
        dropdown.appendChild(option);
    });
}

// Update anggota input in peminjaman modal based on selected kelas
function updateAnggotaDropdown(selectedKelas = null) {
    const input = document.getElementById('namaPeminjam');
    input.value = ''; // Clear the input

    // Store filtered anggota for search
    let filteredAnggota = anggotaData.filter(anggota => anggota.status === 'Aktif');

    if (selectedKelas) {
        filteredAnggota = filteredAnggota.filter(anggota => anggota.kelas === selectedKelas);
    }

    // Store filtered anggota globally for search
    window.filteredAnggota = filteredAnggota;

    // Add search functionality similar to anggota table
    input.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();

        // If search term is empty, clear the kelas dropdown
        if (!searchTerm) {
            document.getElementById('kelasPeminjam').value = '';
        }

        const suggestions = filteredAnggota.filter(anggota =>
            anggota.nama.toLowerCase().includes(searchTerm)
        );

        // Remove existing suggestion list if any
        let suggestionList = document.getElementById('namaPeminjamSuggestions');
        if (suggestionList) suggestionList.remove();

        // Create suggestion list if there are matches
        if (suggestions.length > 0 && searchTerm) {
            suggestionList = document.createElement('ul');
            suggestionList.id = 'namaPeminjamSuggestions';
            suggestionList.className = 'suggestion-list';
            suggestionList.style.cssText = `
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #ddd;
                border-top: none;
                border-radius: 0 0 4px 4px;
                max-height: 200px;
                overflow-y: auto;
                z-index: 1000;
                list-style: none;
                padding: 0;
                margin: 0;
            `;

            suggestions.forEach(anggota => {
                const li = document.createElement('li');
                li.textContent = `${anggota.nama} (${anggota.kelas})`;
                li.style.cssText = `
                    padding: 8px 12px;
                    cursor: pointer;
                    border-bottom: 1px solid #eee;
                `;
                li.addEventListener('click', function() {
                    input.value = anggota.nama;
                    // Also fill in the kelas dropdown
                    const kelasDropdown = document.getElementById('kelasPeminjam');
                    kelasDropdown.value = anggota.kelas;
                    suggestionList.remove();
                });
                li.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                });
                li.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = 'white';
                });
                suggestionList.appendChild(li);
            });

            // Position the list
            const inputRect = input.getBoundingClientRect();
            suggestionList.style.position = 'fixed';
            suggestionList.style.top = `${inputRect.bottom}px`;
            suggestionList.style.left = `${inputRect.left}px`;
            suggestionList.style.width = `${inputRect.width}px`;

            document.body.appendChild(suggestionList);
        }
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target)) {
            const suggestionList = document.getElementById('namaPeminjamSuggestions');
            if (suggestionList) suggestionList.remove();
        }
    });
}

// Add new alat
async function tambahAlat() {
    const nama = document.getElementById('namaAlat').value;
    const deskripsi = document.getElementById('deskripsiAlat').value;
    const kategori = document.getElementById('kategoriAlat').value;
    const stock = parseInt(document.getElementById('stockAlat').value) || 0;
    const fotoInput = document.getElementById('fotoAlat');
    const fotoFile = fotoInput.files[0];

    if (!nama || !deskripsi || !kategori || stock < 0) {
        showCustomAlert('error', 'Error!', 'Harap isi semua field yang wajib diisi dan pastikan stock valid!');
        return;
    }

    const formData = new FormData();
    formData.append('nama', nama);
    formData.append('deskripsi', deskripsi);
    formData.append('kategori', kategori);
    formData.append('stock', stock);
    formData.append('status', 'Tersedia');

    if (fotoFile) {
        formData.append('foto', fotoFile);
    }

    try {
        // Show loading state
        const btn = document.getElementById('simpanAlatBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const response = await fetch(`${API_BASE}/alat`, {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            const newAlat = await response.json();
            
            // Reset form and close modal
            closeAllModals();
            document.getElementById('alatForm').reset();

            // Update alatData array instead of full reload
            if (Array.isArray(alatData)) {
                alatData.unshift(newAlat);
            }

            // Refresh dashboard stats from server
            if (activeTab === 'dashboard') {
                await refreshDashboardStats();
            } else if (activeTab === 'dataAlat') {
                renderDataAlat();
            }

            // Update notification indicator
            checkNotifications();

            showCustomAlert('success', 'Berhasil!', 'Alat berhasil ditambahkan!');
        } else {
            const error = await response.json();
            showCustomAlert('error', 'Error!', error.error || 'Gagal menambahkan alat!');
        }
    } catch (error) {
        console.error('Error adding alat:', error);
        showCustomAlert('error', 'Error!', 'Terjadi kesalahan saat menambahkan alat!');
    } finally {
        // Restore button state
        const btn = document.getElementById('simpanAlatBtn');
        btn.disabled = false;
        btn.innerHTML = originalText || 'Simpan';
    }
}

// Add new anggota
async function tambahAnggota() {
    const nama = document.getElementById('namaAnggota').value;
    const kelas = document.getElementById('kelasAnggota').value;
    const kontak = document.getElementById('kontakAnggota').value;

    if (!nama || !kelas || !kontak) {
        showCustomAlert('error', 'Error!', 'Harap isi semua field yang wajib diisi!');
        return;
    }

    const newAnggota = {
        nama,
        kelas,
        kontak,
        status: 'Aktif'
    };

    try {
        // Show loading state
        const btn = document.getElementById('simpanAnggotaBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const response = await fetch(`${API_BASE}/anggota`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(newAnggota)
        });

        if (response.ok) {
            const addedAnggota = await response.json();
            
            closeAllModals();
            document.getElementById('anggotaForm').reset();

            // Update anggotaData array instead of full reload
            if (Array.isArray(anggotaData)) {
                anggotaData.unshift(addedAnggota);
            }

            // Only refresh the active tab view
            if (activeTab === 'dataAnggota') renderDataAnggota();

            // Update notification indicator
            checkNotifications();

            showCustomAlert('success', 'Berhasil!', 'Anggota berhasil ditambahkan!');
        } else {
            const error = await response.json();
            showCustomAlert('error', 'Error!', error.error || 'Gagal menambahkan anggota!');
        }
    } catch (error) {
        console.error('Error adding anggota:', error);
        showCustomAlert('error', 'Error!', 'Terjadi kesalahan saat menambahkan anggota!');
    } finally {
        // Restore button state
        const btn = document.getElementById('simpanAnggotaBtn');
        btn.disabled = false;
        btn.innerHTML = originalText || 'Simpan';
    }
}

// Add new peminjaman
async function catatPeminjaman() {
    const alatId = parseInt(document.getElementById('alatDipinjam').value);
    const peminjam = document.getElementById('namaPeminjam').value;
    const tanggalMulai = document.getElementById('tanggalMulai').value;
    const tanggalSelesai = document.getElementById('tanggalSelesai').value;

    if (!alatId || !peminjam || !tanggalMulai || !tanggalSelesai) {
        showCustomAlert('error', 'Error!', 'Harap isi semua field yang wajib diisi!');
        return;
    }

    // Validate that peminjam exists in anggotaData with status 'Aktif'
    const anggotaValid = anggotaData.find(a => a.nama === peminjam && a.status === 'Aktif');
    if (!anggotaValid) {
        showCustomAlert('error', 'Error!', 'Nama peminjam tidak terdaftar atau tidak aktif!');
        return;
    }

    const alat = alatData.find(a => a.id === alatId);
    if (!alat || alat.stock <= 0) {
        showCustomAlert('error', 'Error!', 'Alat tidak tersedia untuk dipinjam!');
        return;
    }

    const newPeminjamanData = {
        alat_id: alatId,
        nama_alat: alat.nama,
        peminjam,
        tanggal_mulai: tanggalMulai,
        tanggal_selesai: tanggalSelesai,
        status: 'Aktif',
        keterangan: ''
    };

    try {
        // Show loading state
        const btn = document.getElementById('simpanPeminjamanBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const response = await fetch(`${API_BASE}/peminjaman`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(newPeminjamanData)
        });

        if (response.ok) {
            const addedPeminjaman = await response.json();
            
            closeAllModals();
            document.getElementById('peminjamanForm').reset();

            // Update local data directly without full reload
            peminjamanData.unshift(addedPeminjaman);

            // Decrease stock in alatData
            const alatIndex = alatData.findIndex(a => a.id === alatId);
            if (alatIndex !== -1) {
                alatData[alatIndex].stock -= 1;
            }

            // Refresh dashboard stats from server
            if (activeTab === 'dashboard') {
                await refreshDashboardStats();
            } else if (activeTab === 'dataAlat') {
                renderDataAlat();
            } else if (activeTab === 'peminjaman') {
                renderPeminjaman();
            }

            showCustomAlert('success', 'Berhasil!', 'Peminjaman berhasil dicatat!');
        } else {
            const error = await response.json();
            showCustomAlert('error', 'Error!', error.error || 'Gagal mencatat peminjaman!');
        }
    } catch (error) {
        console.error('Error adding peminjaman:', error);
        showCustomAlert('error', 'Error!', 'Terjadi kesalahan saat mencatat peminjaman!');
    } finally {
        // Restore button state
        const btn = document.getElementById('simpanPeminjamanBtn');
        btn.disabled = false;
        btn.innerHTML = originalText || 'Simpan';
    }
}

// Return alat
async function kembalikanAlat(peminjamanId) {
    try {
        // Show loading state
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

        const response = await fetch(`${API_BASE}/peminjaman/${peminjamanId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ action: 'return' })
        });

        if (response.ok) {
            // Update peminjamanData locally
            const peminjamanIndex = peminjamanData.findIndex(p => p.id === peminjamanId);
            if (peminjamanIndex !== -1) {
                const peminjaman = peminjamanData[peminjamanIndex];
                peminjamanData[peminjamanIndex].status = 'Selesai';

                // Increase stock in alatData
                const alatIndex = alatData.findIndex(a => a.id === peminjaman.alat_id);
                if (alatIndex !== -1) {
                    alatData[alatIndex].stock += 1;
                }
            }

            // Refresh only active tab views without full reload
            if (activeTab === 'dashboard') {
                await refreshDashboardStats();
            } else if (activeTab === 'dataAlat') {
                renderDataAlat();
            } else if (activeTab === 'peminjaman') {
                renderPeminjaman();
            }

            showCustomAlert('success', 'Berhasil!', 'Alat berhasil dikembalikan!');
        } else {
            const error = await response.json();
            showCustomAlert('error', 'Error!', error.error || 'Gagal mengembalikan alat!');
        }
    } catch (error) {
        console.error('Error returning alat:', error);
        showCustomAlert('error', 'Error!', 'Terjadi kesalahan saat mengembalikan alat!');
    } finally {
        if (event.target) {
            event.target.disabled = false;
            event.target.innerHTML = originalText || 'Kembalikan';
        }
    }
}

// Delete alat
async function hapusAlat(alatId) {
    const index = alatData.findIndex(a => a.id === alatId);
    if (index === -1) return;

    // Check if alat is currently borrowed
    const isBorrowed = peminjamanData.some(p => p.alat_id === alatId && p.status === 'Aktif');
    if (isBorrowed) {
        showCustomAlert('error', 'Error!', 'Tidak dapat menghapus alat yang sedang dipinjam!');
        return;
    }

    // Show custom confirmation dialog
    showCustomConfirm('warning', 'Konfirmasi Hapus', 'Apakah Anda yakin ingin menghapus alat ini?', async () => {
        try {
            const response = await fetch(`${API_BASE}/alat/${alatId}`, {
                method: 'DELETE'
            });

            if (response.ok) {
                // Remove alat from local data
                alatData.splice(index, 1);

                // Remove related peminjaman records
                peminjamanData = peminjamanData.filter(p => p.alat_id !== alatId);

                // Refresh dashboard stats from server
                if (activeTab === 'dashboard') {
                    await refreshDashboardStats();
                } else if (activeTab === 'dataAlat') {
                    renderDataAlat();
                } else if (activeTab === 'peminjaman') {
                    renderPeminjaman();
                }

                // Update notification indicator
                checkNotifications();

                showCustomAlert('success', 'Berhasil!', 'Alat berhasil dihapus!');
            } else {
                const error = await response.json();
                showCustomAlert('error', 'Error!', error.error || 'Gagal menghapus alat!');
            }
        } catch (error) {
            console.error('Error deleting alat:', error);
            showCustomAlert('error', 'Error!', 'Terjadi kesalahan saat menghapus alat!');
        }
    });
}

// Delete anggota
async function hapusAnggota(anggotaId) {
    const index = anggotaData.findIndex(a => a.id === anggotaId);
    if (index === -1) return;

    // Check if anggota has active peminjaman
    const hasActivePeminjaman = peminjamanData.some(p => p.peminjam === anggotaData[index].nama && p.status === 'Aktif');
    if (hasActivePeminjaman) {
        showCustomAlert('error', 'Error!', 'Tidak dapat menghapus anggota yang memiliki peminjaman aktif!');
        return;
    }

    // Show custom confirmation dialog
    showCustomConfirm('warning', 'Konfirmasi Hapus', 'Apakah Anda yakin ingin menghapus anggota ini?', async () => {
        try {
            const response = await fetch(`${API_BASE}/anggota/${anggotaId}`, {
                method: 'DELETE'
            });

            if (response.ok) {
                // Remove anggota from local data
                anggotaData.splice(index, 1);

                // Refresh data from API
                await initData();

                // Refresh views
                if (activeTab === 'dataAnggota') renderDataAnggota();

                // Update notification indicator
                checkNotifications();

                showCustomAlert('success', 'Berhasil!', 'Anggota berhasil dihapus!');
            } else {
                const error = await response.json();
                showCustomAlert('error', 'Error!', error.error || 'Gagal menghapus anggota!');
            }
        } catch (error) {
            console.error('Error deleting anggota:', error);
            showCustomAlert('error', 'Error!', 'Terjadi kesalahan saat menghapus anggota!');
        }
    });
}

// Custom Alert Functions
function showCustomAlert(type, title, message) {
    const alertModal = document.createElement('div');
    alertModal.className = 'custom-alert';
    alertModal.innerHTML = `
        <div class="custom-alert-content">
            <div class="custom-alert-icon ${type}">
                <i class="fas ${getIconForType(type)}"></i>
            </div>
            <h2 class="custom-alert-title">${title}</h2>
            <p class="custom-alert-message">${message}</p>
            <button class="custom-alert-btn" onclick="closeCustomAlert(this)">OK</button>
        </div>
    `;
    document.body.appendChild(alertModal);

    // Trigger animation
    setTimeout(() => {
        alertModal.classList.add('show');
    }, 10);
}

function closeCustomAlert(button) {
    const alertModal = button.closest('.custom-alert');
    alertModal.classList.remove('show');
    setTimeout(() => {
        document.body.removeChild(alertModal);
    }, 300);
}

function getIconForType(type) {
    switch(type) {
        case 'success': return 'fa-check';
        case 'warning': return 'fa-exclamation-triangle';
        case 'error': return 'fa-times';
        default: return 'fa-info';
    }
}

// Custom Confirm Functions
let currentConfirmCallback = null;

function showCustomConfirm(type, title, message, onConfirm) {
    currentConfirmCallback = onConfirm;
    const confirmModal = document.createElement('div');
    confirmModal.className = 'custom-alert';
    confirmModal.innerHTML = `
        <div class="custom-alert-content">
            <div class="custom-alert-icon ${type}">
                <i class="fas ${getIconForType(type)}"></i>
            </div>
            <h2 class="custom-alert-title">${title}</h2>
            <p class="custom-alert-message">${message}</p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button class="custom-alert-btn" onclick="closeCustomConfirm(this, false)">Batal</button>
                <button class="custom-alert-btn" onclick="closeCustomConfirm(this, true)">Ya, Hapus</button>
            </div>
        </div>
    `;
    document.body.appendChild(confirmModal);

    // Trigger animation
    setTimeout(() => {
        confirmModal.classList.add('show');
    }, 10);
}

function closeCustomConfirm(button, confirmed) {
    const confirmModal = button.closest('.custom-alert');
    confirmModal.classList.remove('show');
    setTimeout(() => {
        document.body.removeChild(confirmModal);
        if (confirmed && currentConfirmCallback) {
            currentConfirmCallback();
            currentConfirmCallback = null;
        }
    }, 300);
}

// Check for notifications and update button indicator
function checkNotifications() {
    const notifications = [];

    // Check for low stock items
    alatData.forEach(alat => {
        if (alat.stock <= 2 && alat.stock > 0) {
            notifications.push({
                type: 'warning',
                title: 'Stock Rendah',
                message: `${alat.nama} memiliki stock rendah (${alat.stock} unit tersisa)`,
                time: '5 menit yang lalu'
            });
        } else if (alat.stock === 0) {
            notifications.push({
                type: 'error',
                title: 'Stock Habis',
                message: `${alat.nama} sudah habis stock`,
                time: '10 menit yang lalu'
            });
        }
    });

    // Check for overdue returns
    const today = new Date();
    peminjamanData.forEach(peminjaman => {
        if (peminjaman.status === 'Aktif') {
            const tanggalSelesai = new Date(peminjaman.tanggalSelesai);
            if (tanggalSelesai < today) {
                notifications.push({
                    type: 'warning',
                    title: 'Peminjaman Terlambat',
                    message: `${peminjaman.namaAlat} dipinjam oleh ${peminjaman.peminjam} sudah melewati batas waktu`,
                    time: '1 jam yang lalu'
                });
            }
        }
    });

    // Check for upcoming due dates (within 3 days)
    peminjamanData.forEach(peminjaman => {
        if (peminjaman.status === 'Aktif') {
            const tanggalSelesai = new Date(peminjaman.tanggalSelesai);
            const diffTime = tanggalSelesai - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays <= 3 && diffDays >= 0) {
                notifications.push({
                    type: 'info',
                    title: 'Pengembalian Mendekati',
                    message: `${peminjaman.namaAlat} dipinjam oleh ${peminjaman.peminjam} harus dikembalikan dalam ${diffDays} hari`,
                    time: '2 jam yang lalu'
                });
            }
        }
    });

    // Update notification button indicator
    const notificationBtn = document.querySelector('.notification-btn');
    if (notifications.length > 0 && notifications[0].type !== 'success') {
        notificationBtn.classList.add('has-notifications');
    } else {
        notificationBtn.classList.remove('has-notifications');
    }

    return notifications;
}

// Show notifications
function showNotifications() {
    const notifications = checkNotifications();

    // If no notifications, show a default message
    if (notifications.length === 0) {
        notifications.push({
            type: 'success',
            title: 'Semua Baik',
            message: 'Tidak ada notifikasi penting saat ini',
            time: 'Baru saja'
        });
    }

    // Create notification dropdown
    const notificationDropdown = document.createElement('div');
    notificationDropdown.className = 'notification-dropdown';
    notificationDropdown.innerHTML = `
        <div class="notification-header">
            <h3>Notifikasi</h3>
            <span class="notification-count">${notifications.length}</span>
        </div>
        <div class="notification-list">
            ${notifications.map(notification => `
                <div class="notification-item ${notification.type}">
                    <div class="notification-icon">
                        <i class="fas ${getNotificationIcon(notification.type)}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">${notification.time}</div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    // Position the dropdown
    const notificationBtn = document.querySelector('.notification-btn');
    const rect = notificationBtn.getBoundingClientRect();
    notificationDropdown.style.position = 'fixed';
    notificationDropdown.style.top = `${rect.bottom + 10}px`;
    notificationDropdown.style.right = `${window.innerWidth - rect.right}px`;
    notificationDropdown.style.zIndex = '1000';

    // Add to body
    document.body.appendChild(notificationDropdown);

    // Add click outside to close
    setTimeout(() => {
        document.addEventListener('click', function closeNotification(e) {
            if (!notificationDropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
                document.body.removeChild(notificationDropdown);
                document.removeEventListener('click', closeNotification);
            }
        });
    }, 10);
}

// Get notification icon based on type
function getNotificationIcon(type) {
    switch(type) {
        case 'warning': return 'fa-exclamation-triangle';
        case 'error': return 'fa-times-circle';
        case 'info': return 'fa-info-circle';
        case 'success': return 'fa-check-circle';
        default: return 'fa-bell';
    }
}

// Toggle sidebar for mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.menu-toggle');

    sidebar.classList.toggle('active');

    // Add or remove back button inside sidebar based on state
    if (sidebar.classList.contains('active')) {
        // Hide the header menu toggle
        menuToggle.style.display = 'none';

        // Add back button inside sidebar
        const backButton = document.createElement('button');
        backButton.className = 'sidebar-back-btn';
        backButton.innerHTML = '<i class="fas fa-arrow-left"></i> Kembali';
        backButton.onclick = toggleSidebar;

        // Insert at the top of sidebar menu
        const sidebarMenu = document.querySelector('.sidebar-menu');
        sidebarMenu.insertBefore(backButton, sidebarMenu.firstChild);
    } else {
        // Show the header menu toggle
        menuToggle.style.display = 'flex';
        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';

        // Remove back button from sidebar
        const backBtn = document.querySelector('.sidebar-back-btn');
        if (backBtn) {
            backBtn.remove();
        }
    }
}

// Format date to local string
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}
