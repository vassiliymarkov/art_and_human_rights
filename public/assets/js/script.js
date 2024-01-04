"use strict"

/*||| CHECKING DEVICE TYPE |||/ */

const isMobile = {
	Android: function () {
		return navigator.userAgent.match(/Android/i);
	},
	BlackBerry: function () {
		return navigator.userAgent.match(/BlackBerry/i);
	},
	iOS: function () {
		return navigator.userAgent.match(/iPhone|iPad|iPod/i);
	},
	Opera: function () {
		return navigator.userAgent.match(/Opera Mini/i);
	},
	Windows: function () {
		return navigator.userAgent.match(/Edge/i);
	},
	any: function () {
		return (
			isMobile.Android() ||
			isMobile.BlackBerry() ||
			isMobile.iOS() ||
			isMobile.Opera() ||
			isMobile.Windows());
	}
};

/* IF MOBILE SHOW ARROW IN THE MENU */

if (isMobile.any()) {
	document.body.classList.add('_touch');

	let menuArrows = document.querySelectorAll('.menu-arrow');
	if (menuArrows.length > 0) {
		for (let index = 0; index < menuArrows.length; index++) {
			const menuArrow = menuArrows[index];
			menuArrow.addEventListener("click", function (e) {
				menuArrow.parentElement.classList.toggle('_active');
			});
		}
	}

} else {
	document.body.classList.add('_pc');
}

/*||| BURGER MENU |||*/

const iconMenu = document.querySelector('.menu-icon');
const menuBody = document.querySelector('.menu-body');
if (iconMenu) {
	iconMenu.addEventListener("click", function (e) {
		document.body.classList.toggle('_lock');
		iconMenu.classList.toggle('_active');
		menuBody.classList.toggle('_active');
	});
}


/*||| ACTIVE LINKS |||*/

document.addEventListener('DOMContentLoaded', () => {
	let currentUrl = window.location.href;
	let menuLinks = document.querySelectorAll('.menu-link');

	menuLinks.forEach((link) => {
		let linkCategory = link.getAttribute('data-category');
		let linkHref = link.getAttribute('href');
		let urlParams = new URLSearchParams(currentUrl);

		if (linkCategory && (urlParams.has('category') && urlParams.get('category') === linkCategory || currentUrl.includes(linkHref))) {
			link.classList.add('active');
		}
	});
});

/*||| MODAL WINDOWS FOR IMAGES ||| */

// Get the modal and image elements
let modal = document.getElementById("myModal");
let modalImage = document.getElementById("modalImage");

// Get all elements with the class "image-full"
let images = document.querySelectorAll(".image-full");

// Add click event listeners to each image
for (let i = 0; i < images.length; i++) {
	images[i].addEventListener("click", function (event) {
		modal.style.display = "flex";
		document.body.style.overflow = "hidden";
		modalImage.src = this.src;
	});
}

// Get the close button element
let span = document.querySelector('.close');

// Add click event listener to the close button
if (span) {
	span.addEventListener("click", function () {
		modal.style.display = "none";
		document.body.style.overflow = "auto";
	});
}

// Add click event listener to the window
window.addEventListener("click", function (event) {
	if (event.target == modal) {
		modal.style.display = "none";
		document.body.style.overflow = "auto";
	}
});


/* ADMIN PANEL */

document.addEventListener("DOMContentLoaded", function () {
    const deleteArticleLinks = document.querySelectorAll(".delete-link");

    deleteArticleLinks.forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            const confirmDelete = confirm("Вы уверены, что хотите удалить эту статью?");
            if (confirmDelete) {
                window.location.href = link.getAttribute("href");
            }
        });
    })});