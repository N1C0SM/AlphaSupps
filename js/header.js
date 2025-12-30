const toggle = document.getElementById("menu-toggle");
const sidebar = document.getElementById("adminSidebar");

toggle.addEventListener("click", () => {
  sidebar?.classList.toggle("active");
});
