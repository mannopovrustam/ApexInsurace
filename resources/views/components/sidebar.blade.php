<div class="vertical-menu">

    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="/" class="logo logo-dark">
            <span class="logo-sm">
                <img src="/assets/images/logo-dark.png" alt=""  style="height: 50px; width: 50px">
            </span>
            <span class="logo-lg">
                <img src="/assets/images/logo-dark.png" alt="" style="height: 50px; margin-top: 10px; width: 150px">
            </span>
        </a>

        <a href="/" class="logo logo-light">
            <span class="logo-sm">
                <img src="/assets/images/logo-dark.png" alt="" style="height: 50px; width: 50px">
            </span>
            <span class="logo-lg">
                <img src="/assets/images/logo-dark.png" alt="" style="height: 50px; margin-top: 10px; width: 150px">
            </span>
        </a>
    </div>

    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect vertical-menu-btn">
        <i class="fa fa-fw fa-bars"></i>
    </button>

    <div data-simplebar class="sidebar-menu-scroll">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title">Menu</li>

                <li>
                    <a href="{{route('dashboard')}}">
                        <i class="uil-home-alt"></i>
                        <span>Главная</span>
                    </a>
                </li>

                <li>
                    <a href="/contracts">
                        <i class="fa fa-paperclip"></i>
                        <span>Ҳужжатлар</span>
                    </a>
                </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="uil-window-section"></i>
                        <span>Маълумотнома</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li><a href="/dic/regions">Вилоят/Туман</a></li>
                        @can('Судларни қўшиш/тахрирлаш')
                        <li><a href="/dic/judges">Судлар</a></li>
                        @endcan
                        @can('СМС шаблон яратиш')
                        <li><a href="/dic/sms">Смс шаблон</a></li>
                        @endcan
                        @can('Ҳужжат шаблон яратиш')
                        <li><a href="/dic/docs">Word шаблон</a></li>
                        @endcan
                        <li><a href="/dic/category">Иш туркуми</a></li>
                    </ul>
                </li>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
