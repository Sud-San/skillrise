'use strict';

/* ===== Enable Bootstrap Popover (on element  ====== */
const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

/* ==== Enable Bootstrap Alert ====== */
//var alertList = document.querySelectorAll('.alert')
//alertList.forEach(function (alert) {
//  new bootstrap.Alert(alert)
//});

const alertList = document.querySelectorAll('.alert')
const alerts = [...alertList].map(element => new bootstrap.Alert(element))


/* ===== Responsive Sidepanel ====== */
const sidePanelToggler = document.getElementById('sidepanel-toggler'); 
const sidePanel = document.getElementById('app-sidepanel');  
const sidePanelDrop = document.getElementById('sidepanel-drop'); 
const sidePanelClose = document.getElementById('sidepanel-close'); 

window.addEventListener('load', function(){
	responsiveSidePanel(); 
});

window.addEventListener('resize', function(){
	responsiveSidePanel(); 
});


function responsiveSidePanel() {
    let w = window.innerWidth;
	if(w >= 1200) {
	    // if larger 
	    //console.log('larger');
		sidePanel.classList.remove('sidepanel-hidden');
		sidePanel.classList.add('sidepanel-visible');
		
	} else {
	    // if smaller
	    //console.log('smaller');
	    sidePanel.classList.remove('sidepanel-visible');
		sidePanel.classList.add('sidepanel-hidden');
	}
};

sidePanelToggler.addEventListener('click', () => {
	if (sidePanel.classList.contains('sidepanel-visible')) {
		console.log('visible');
		sidePanel.classList.remove('sidepanel-visible');
		sidePanel.classList.add('sidepanel-hidden');
		
	} else {
		console.log('hidden');
		sidePanel.classList.remove('sidepanel-hidden');
		sidePanel.classList.add('sidepanel-visible');
	}
});



sidePanelClose.addEventListener('click', (e) => {
	e.preventDefault();
	sidePanelToggler.click();
});

sidePanelDrop.addEventListener('click', (e) => {
	sidePanelToggler.click();
});



/* ====== Mobile search ======= */
// ── app.js ──

document.addEventListener('DOMContentLoaded', function () {

    // ── Search mobile trigger ──
    /*const searchMobileTrigger = document.querySelector('.search-mobile-trigger');
    const searchBox = document.querySelector('.search-box'); // adjust selector to match your HTML

    if (searchMobileTrigger && searchBox) {
        searchMobileTrigger.addEventListener('click', () => {
            searchBox.classList.toggle('is-visible');

            const searchMobileTriggerIcon = document.querySelector('.search-mobile-trigger-icon');

            if (searchMobileTriggerIcon) {
                if (searchMobileTriggerIcon.classList.contains('fa-magnifying-glass')) {
                    searchMobileTriggerIcon.classList.remove('fa-magnifying-glass');
                    searchMobileTriggerIcon.classList.add('fa-xmark');
                } else {
                    searchMobileTriggerIcon.classList.remove('fa-xmark');
                    searchMobileTriggerIcon.classList.add('fa-magnifying-glass');
                }
            }
        });
    }*/

    // ── Sidebar toggler ──
    const sidepanelToggler = document.getElementById('sidepanel-toggler');
    const sidepanel        = document.getElementById('app-sidepanel');
    const sidepanelDrop    = document.getElementById('sidepanel-drop');
    const sidepanelClose   = document.getElementById('sidepanel-close');

    if (sidepanelToggler && sidepanel) {
        sidepanelToggler.addEventListener('click', function (e) {
            e.preventDefault();
            sidepanel.classList.toggle('sidepanel-visible');
        });
    }

    if (sidepanelDrop && sidepanel) {
        sidepanelDrop.addEventListener('click', function () {
            sidepanel.classList.remove('sidepanel-visible');
        });
    }

    if (sidepanelClose && sidepanel) {
        sidepanelClose.addEventListener('click', function (e) {
            e.preventDefault();
            sidepanel.classList.remove('sidepanel-visible');
        });
    }

    // ── Auto-open active submenu ──
    document.querySelectorAll('.submenu-link').forEach(function (link) {
        if (link.href === window.location.href) {
            link.classList.add('active');
            const collapseEl = link.closest('.collapse');
            if (collapseEl) {
                bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
            }
        }
    });

    // ── Init all Bootstrap dropdowns manually ──
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
        new bootstrap.Dropdown(el);
    });

});

// ── Logout confirmation (must be global for onclick to work) ──
function confirmLogout() {
    if (confirm('Are you sure you want to log out?')) {
        window.location.href = 'login.php';
    }
}


