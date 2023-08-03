
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/owl.carousel.css">
    <link rel="stylesheet" href="/css/animate.css">
    <link rel="stylesheet" href="/css/main.css">
    <title>Imperial</title>
</head>
<body class="ru">

@if(\Request::segment(1) == '' || \Request::segment(1) == 'view')
<!-- PRELOADER -->
{{--<div class="preloader">
    <div class="preloader__logo">
        <lottie-player class="ico" src="js/lottie.json" background="transparent" speed="1" autoplay loop></lottie-player>
    </div>
    <div class="preloader__percent">0</div>
</div>--}}

<!-- cube button-->

<a href="#" class="cube-button">
    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1.46 4.26376L6.76 7.33376L12.02 4.28376M6.76 12.7738V7.32376M5.52 1.29376L2.32 3.07376C1.6 3.47376 1 4.48376 1 5.31376V8.70376C1 9.53376 1.59 10.5438 2.32 10.9438L5.52 12.7238C6.2 13.1038 7.32 13.1038 8.01 12.7238L11.21 10.9438C11.93 10.5438 12.53 9.53376 12.53 8.70376V5.30376C12.53 4.47376 11.94 3.46376 11.21 3.06376L8.01 1.28376C7.32 0.903762 6.2 0.903762 5.52 1.29376Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</a>

@endif

<!-- MENU -->

<div class="menu">
    <div class="menu-wrap">
        <div class="container">
            <ul class="menu-list">
                <li>
                    <a href="/#main">
                        О ПРОЕКТЕ
                    </a>
                </li>
                <li>
                    <a href="/#advantages">
                        Особенности
                    </a>
                </li>
                <li>
                    <a href="/#flat">
                        ПЛАНИРОВКИ
                    </a>
                </li>
                <li>
                    <a href="/#news">
                        НОВОСТИ
                    </a>
                </li>
                <li>
                    <a href="/#newmon">
                        О ЗАСТРОЙЩИКЕ
                    </a>
                </li>
                <li>
                    <a href="/#footer">
                        КОНТАКТЫ
                    </a>
                </li>
            </ul>

        </div>
        <ul class="menu-social">
            <li>
                <a href="#" target="_blank">
                    instagram
                </a>
            </li>
            <li>
                <a href="#" target="_blank">
                    facebook
                </a>
            </li>
            <li>
                <a href="#" target="_blank">
                    telegram
                </a>
            </li>
        </ul>
        <a href="/view/project" class="menu__btn">
            ВЫБРАТЬ КВАРТИРУ
        </a>
    </div>
</div>

<!-- HEADER -->

<header class="header @yield('h_class')">
    <div class="container">
        <div class="header-left">
            <div class="header-menu">
                <div class="header-menu__btn header-menu__open">
                    <svg width="16" height="10" viewBox="0 0 16 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 1H16" stroke="currentColor"/>
                        <path d="M0 5H16" stroke="currentColor"/>
                        <path d="M0 9H16" stroke="currentColor"/>
                    </svg>
                    <span>МЕНЮ</span>
                </div>
                <div class="header-menu__btn header-menu__close">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.34375 12.6572L12.6575 1.34352" stroke="white"/>
                        <path d="M1.34375 1.34375L12.6575 12.6575" stroke="white"/>
                    </svg>
                    <span>ЗАКРЫТЬ</span>
                </div>
            </div>
            <a href="#" class="header__link">
                СВЯЗАТЬСЯ С НАМИ
            </a>
        </div>
        <a href="/" class="header__logo">
            <img src="/img/logo.svg" alt="Imperial" title="Imperial">
        </a>
        <div class="header-right">
            <div class="header-lang">
                <a href="/languages/ru" class="header__link @if(app()->getLocale() == 'ru') current @endif">
                    ru
                </a>
                <a href="/languages/uz" class="header__link @if(app()->getLocale() == 'uz') current @endif">
                    uz
                </a>
                <a href="/languages/en" class="header__link @if(app()->getLocale() == 'en') current @endif">
                    eng
                </a>
            </div>
            <a href="tel:+998958907777" class="header-tel">
                + 998 95 890 77 77
            </a>
        </div>
    </div>
</header>

@yield('content')

<!-- FOOTER -->

<footer class="footer" id="footer">
    <a href="/" class="footer__logo">
        <img src="/img/logo.svg" alt="Imperial" title="Imperial">
    </a>
    <ul class="footer-menu">
        <li>
            <a href="#">О проекте</a>
        </li>
        <li>
            <a href="#">Особенности</a>
        </li>
        <li>
            <a href="#">Инфраструктура</a>
        </li>
        <li>
            <a href="/reports">Новости</a>
        </li>
        <li>
            <a href="/contacts">Контакты</a>
        </li>
    </ul>
    <a href="tel:958907777" class="footer__tel">
        95 890 77 77
    </a>
    <div class="footer__text">
        ПРЕЗЕНТАЦИОННЫЙ ОФИС: 5-й проезд Ниёзбек йули, 29А (ориентир: Алайский рынок)
    </div>
    <div class="footer-links">
        <a href="#">instagram</a>
        <a href="#">facebook</a>
        <a href="#" class="nova">created by NOVAS</a>
    </div>
</footer>
<div class="mcrm-inline-form" data-type="catalog"></div>

<script src="/js/jquery-3.4.1.min.js"></script>
<script src="/js/jquery.inputmask.min.js"></script>
<script src="/js/owl.carousel.js"></script>
<script src="/js/jquery.nicescroll.min.js"></script>
<script src="/js/wow.min.js"></script>
<script src="/js/main.js"></script>

@yield('scripts')

<script type="text/javascript">(function (d, w) {var n = d.getElementsByTagName("script")[0], s = d.createElement("script"); s.type = "text/javascript"; s.async = true; s.src = "https://api.macroserver.ru/estate/embedjs/?domain="+window.location.host; n.parentNode.insertBefore(s, n)})(document, window)</script>
</body>
</html>
