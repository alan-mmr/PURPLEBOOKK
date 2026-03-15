<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">

        {{-- Profile Section (seperti template) --}}
        <li class="nav-item nav-profile">
            <a href="#" class="nav-link">
                <div class="nav-profile-image">
                    <img src="{{ asset('assets/images/faces/face1.jpg') }}" alt="profile">
                    <span class="login-status online"></span>
                </div>
                <div class="nav-profile-text d-flex flex-column">
                    <span class="font-weight-bold mb-2">{{ Auth::user()->name }}</span>
                    <span class="text-secondary text-small">Administrator</span>
                </div>
                <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
            </a>
        </li>

        {{-- Dashboard --}}
        <li class="nav-item {{ request()->is('/') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <span class="menu-title">Dashboard</span>
                <i class="mdi mdi-home menu-icon"></i>
            </a>
        </li>

        {{-- Kategori Management --}}
        <li class="nav-item {{ request()->is('kategori*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('kategori.index') }}">
                <span class="menu-title">Kategori</span>
                <i class="mdi mdi-label-outline menu-icon"></i>
            </a>
        </li>

        {{-- Buku Management --}}
        <li class="nav-item {{ request()->is('buku*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('buku.index') }}">
                <span class="menu-title">Buku</span>
                <i class="mdi mdi-book-open-page-variant menu-icon"></i>
            </a>
        </li>

        {{-- Barang Management --}}
        <li class="nav-item {{ request()->is('barang') || request()->is('barang/create') || request()->is('barang/*/edit') || request()->is('barang/*') && !request()->is('diskon*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('barang.index') }}">
                <span class="menu-title">Barang</span>
                <i class="mdi mdi-tag-multiple menu-icon"></i>
            </a>
        </li>

        {{-- Diskon Barang (SK2 & SK3) --}}
        <li class="nav-item {{ request()->is('diskon') || request()->is('diskon-datatables') ? 'active' : '' }}"
            id="diskonParent">
            <a class="nav-link" href="javascript:void(0)" onclick="toggleDiskonMenu(event)">
                <span class="menu-title">Diskon Barang</span>
                <i class="mdi mdi-chevron-right menu-icon" id="diskonArrow"
                   style="font-size:18px; transition:transform 0.2s;"></i>
            </a>
            <div id="diskonMenu" style="{{ request()->is('diskon') || request()->is('diskon-datatables') ? '' : 'display:none;' }}">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('diskon') ? 'active' : '' }}"
                           href="{{ route('diskon.html') }}">HTML</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('diskon-datatables') ? 'active' : '' }}"
                           href="{{ route('diskon.datatables') }}">DataTables</a>
                    </li>
                </ul>
            </div>
        </li>
        <script>
        // Rotate arrow sesuai state saat page load
        (function() {
            var menu = document.getElementById('diskonMenu');
            var arrow = document.getElementById('diskonArrow');
            if (menu && menu.style.display !== 'none') {
                arrow.style.transform = 'rotate(90deg)';
            }
        })();

        function toggleDiskonMenu(e) {
            e.preventDefault();
            e.stopPropagation();
            var menu  = document.getElementById('diskonMenu');
            var arrow = document.getElementById('diskonArrow');
            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display  = 'block';
                arrow.style.transform = 'rotate(90deg)';  // ↓
            } else {
                menu.style.display  = 'none';
                arrow.style.transform = 'rotate(0deg)';   // →
            }
        }
        </script>


        {{-- Kota (SK4) --}}
        <li class="nav-item {{ request()->is('kota*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('kota.index') }}">
                <span class="menu-title">Kota</span>
                <i class="mdi mdi-map-marker-multiple menu-icon"></i>
            </a>
        </li>

        {{-- Administrasi Wilayah (Studi Kasus Baru) --}}
        <li class="nav-item {{ request()->is('administrasi') || request()->is('administrasi-axios') ? 'active' : '' }}"
            id="administrasiParent">
            <a class="nav-link" href="javascript:void(0)" onclick="toggleAdminMenu(event)">
                <span class="menu-title">Administrasi</span>
                <i class="mdi mdi-chevron-right menu-icon" id="administrasiArrow"
                   style="font-size:18px; transition:transform 0.2s;"></i>
            </a>
            <div id="administrasiMenu" style="{{ request()->is('administrasi') || request()->is('administrasi-axios') ? '' : 'display:none;' }}">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('administrasi') ? 'active' : '' }}"
                           href="{{ route('administrasi.ajax') }}">AJAX</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('administrasi-axios') ? 'active' : '' }}"
                           href="{{ route('administrasi.axios') }}">Axios</a>
                    </li>
                </ul>
            </div>
        </li>
        <script>
        (function() {
            var menu  = document.getElementById('administrasiMenu');
            var arrow = document.getElementById('administrasiArrow');
            if (menu && menu.style.display !== 'none') arrow.style.transform = 'rotate(90deg)';
        })();
        function toggleAdminMenu(e) {
            e.preventDefault(); e.stopPropagation();
            var menu  = document.getElementById('administrasiMenu');
            var arrow = document.getElementById('administrasiArrow');
            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display  = 'block';
                arrow.style.transform = 'rotate(90deg)';
            } else {
                menu.style.display  = 'none';
                arrow.style.transform = 'rotate(0deg)';
            }
        }
        </script>

        {{-- Point Of Sales (Studi Kasus Baru) --}}
        <li class="nav-item {{ request()->is('pos') || request()->is('pos-axios') ? 'active' : '' }}"
            id="posParent">
            <a class="nav-link" href="javascript:void(0)" onclick="togglePosMenu(event)">
                <span class="menu-title">Point Of Sales</span>
                <i class="mdi mdi-chevron-right menu-icon" id="posArrow"
                   style="font-size:18px; transition:transform 0.2s;"></i>
            </a>
            <div id="posMenu" style="{{ request()->is('pos') || request()->is('pos-axios') ? '' : 'display:none;' }}">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('pos') ? 'active' : '' }}"
                           href="{{ route('pos.ajax') }}">AJAX</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('pos-axios') ? 'active' : '' }}"
                           href="{{ route('pos.axios') }}">Axios</a>
                    </li>
                </ul>
            </div>
        </li>
        <script>
        (function() {
            var menu  = document.getElementById('posMenu');
            var arrow = document.getElementById('posArrow');
            if (menu && menu.style.display !== 'none') arrow.style.transform = 'rotate(90deg)';
        })();
        function togglePosMenu(e) {
            e.preventDefault(); e.stopPropagation();
            var menu  = document.getElementById('posMenu');
            var arrow = document.getElementById('posArrow');
            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display  = 'block';
                arrow.style.transform = 'rotate(90deg)';
            } else {
                menu.style.display  = 'none';
                arrow.style.transform = 'rotate(0deg)';
            }
        }
        </script>

        {{-- PDF Generation (Studi Kasus 2) --}}
        <li class="nav-item {{ request()->is('pdf*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('pdf.index') }}">
                <span class="menu-title">Cetak Dokumen</span>
                <i class="mdi mdi-file-pdf-box menu-icon"></i>
            </a>
        </li>

    </ul>
</nav>

