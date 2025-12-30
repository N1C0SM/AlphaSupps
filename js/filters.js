document.addEventListener("DOMContentLoaded", () => {

  /* ===== FILTRO POR MARCAS ===== */
  const cards = document.querySelectorAll('.card');
  const buttons = document.querySelectorAll('.filters button');
  const noResults = document.querySelector('.no-results');

  if (buttons.length > 0) {
    buttons.forEach(btn => {
      btn.addEventListener('click', () => {

        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const brand = btn.dataset.brand;
        let visible = 0;

        cards.forEach(card => {
          if (brand === "todas" || card.dataset.brand === brand) {
            card.style.display = "block";
            visible++;
          } else {
            card.style.display = "none";
          }
        });

        if (noResults) {
          noResults.style.display = visible === 0 ? 'block' : 'none';
        }
      });
    });
  }

  /* ===== ORDENAR ===== */
  const orderSelect = document.getElementById('order');

  if (orderSelect) {
    orderSelect.addEventListener('change', () => {

      const container = document.querySelector('.cards');
      if (!container) return;

      const elements = Array.from(cards);
      let sorted = [...elements];

      switch (orderSelect.value) {

        case "price_low":
          sorted.sort((a, b) => a.dataset.price - b.dataset.price);
          break;

        case "price_high":
          sorted.sort((a, b) => b.dataset.price - a.dataset.price);
          break;

        case "recent":
          sorted.sort(
            (a, b) => new Date(b.dataset.created) - new Date(a.dataset.created)
          );
          break;

        default:
          return;
      }

      sorted.forEach(card => container.appendChild(card));
    });
  }

});