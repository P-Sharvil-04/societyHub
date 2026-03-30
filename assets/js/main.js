// Sidebar toggle (mobile/desktop)
const sidebar = document.getElementById("sidebar");
const mainEl = document.getElementById("main");
const header = document.getElementById("header");
const hamburger = document.getElementById("hamburger");
const overlay = document.getElementById("overlay");

function handleSidebar() {
	if (window.innerWidth > 768) {
		sidebar.classList.toggle("collapsed");
		mainEl.classList.toggle("collapsed");
		header.classList.toggle("collapsed");
	} else {
		sidebar.classList.toggle("active");
		overlay.classList.toggle("active");
	}
}

if (hamburger) {
	hamburger.addEventListener("click", handleSidebar);
}
if (overlay) {
	overlay.addEventListener("click", () => {
		sidebar.classList.remove("active");
		overlay.classList.remove("active");
	});
}
window.addEventListener("resize", function () {
	if (window.innerWidth > 768) {
		sidebar.classList.remove("active");
		overlay.classList.remove("active");
	}
});

// Theme toggle
const themeToggle = document.getElementById("themeToggle");
const savedTheme = localStorage.getItem("theme") || "light";
if (savedTheme === "dark") {
	document.body.classList.add("dark-mode");
	if (themeToggle)
		themeToggle.innerHTML = '<i class="fas fa-sun"></i><span>Light</span>';
}

if (themeToggle) {
	themeToggle.addEventListener("click", () => {
		document.body.classList.toggle("dark-mode");
		const isDark = document.body.classList.contains("dark-mode");
		themeToggle.innerHTML = isDark
			? '<i class="fas fa-sun"></i><span>Light</span>'
			: '<i class="fas fa-moon"></i><span>Dark</span>';
		localStorage.setItem("theme", isDark ? "dark" : "light");
		// Dispatch custom event for charts (if needed)
		document.dispatchEvent(
			new CustomEvent("themeChanged", { detail: { isDark } }),
		);
	});
}

// Force mobile sidebar initial state
if (window.innerWidth <= 768) sidebar.classList.remove("collapsed");
