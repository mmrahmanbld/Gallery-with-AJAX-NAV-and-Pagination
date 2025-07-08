document.addEventListener("DOMContentLoaded", function () {
    let fixedElement = document.querySelector(".sticky-fixed-menu");
    let isContactPage = document.body.classList.contains("page-id-2886");
    let lastScrollContainer = document.querySelector(isContactPage ? "#footer-last-scroll" : "#last-scroll");

    if (!fixedElement || !lastScrollContainer) {
        console.warn("Fixed element or last-scroll container not found.");
        return;
    }

    function handleScroll() {
        let lastScrollRect = lastScrollContainer.getBoundingClientRect();
        let viewportHeight = window.innerHeight;

        if (lastScrollRect.top <= viewportHeight && lastScrollRect.bottom >= 0) {
            let scrollFactor = Math.min(1, (viewportHeight - lastScrollRect.top) / viewportHeight);
            let translateY = Math.min(scrollFactor * 1100, 1100);

            fixedElement.style.transform = `translateY(-${translateY}px)`;
            fixedElement.style.opacity = `${1 - scrollFactor}`;
        } else {
            fixedElement.style.transform = "translateY(0)";
            fixedElement.style.opacity = "1";
        }
    }

    window.addEventListener("scroll", handleScroll);
});






jQuery(document).ready(function ($) {
    let $menu = $("#sticky-menu-id");

    if (!$menu.length) {
        console.warn("Sticky menu not found.");
        return;
    }

    // Remove Elementor hover behavior
    $menu.find(".elementor-item.has-submenu").off("mouseenter mouseleave");

    // Handle submenu toggle on click
    $menu.on("click", ".elementor-item.has-submenu", function (e) {
        e.preventDefault(); // Prevent default anchor click behavior

        let $parentItem = $(this).closest(".menu-item-has-children");
        let $submenu = $parentItem.children(".sub-menu, .elementor-nav-menu--dropdown");

        if ($submenu.length) {
            if ($parentItem.hasClass("submenu-active")) {
                // If submenu is open, close it
                $submenu.slideUp(300, function () {
                    $(this).css({ visibility: "hidden", opacity: 0 });
                });
                $parentItem.removeClass("submenu-active");
            } else {
                // Close all other submenus before opening this one
                $menu.find(".menu-item-has-children").removeClass("submenu-active")
                    .children(".sub-menu, .elementor-nav-menu--dropdown").slideUp(300, function () {
                        $(this).css({ visibility: "hidden", opacity: 0 });
                    });

                // Open clicked submenu
                $submenu.css({ visibility: "visible", opacity: 1 }).slideDown(300);
                $parentItem.addClass("submenu-active");
            }
        }
    });

    // Prevent submenu from closing when clicking inside it
    $menu.on("click", ".sub-menu a, .elementor-nav-menu--dropdown a", function (e) {
        e.stopPropagation();
    });

    // Close all submenus when clicking outside the menu
    $(document).on("click", function (e) {
        if (!$(e.target).closest("#sticky-menu-id").length) {
            $menu.find(".menu-item-has-children").removeClass("submenu-active")
                .children(".sub-menu, .elementor-nav-menu--dropdown").slideUp(300, function () {
                    $(this).css({ visibility: "hidden", opacity: 0 });
                });
        }
    });

    // Remove the "highlighted" class Elementor adds on hover
    $menu.find(".elementor-item.has-submenu").removeClass("highlighted");
});










