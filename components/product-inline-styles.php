<?php // Inline styles compartidos para las páginas de detalle de producto ?>
<style>
.product-gallery {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.gallery-main {
  position: relative;
  background: #111;
  border-radius: 20px;
  min-height: 320px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gallery-main img {
  width: 100%;
  max-height: 460px;
  object-fit: contain;
  padding: 14px;
}

.gallery-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #fff;
  font-size: 1.4rem;
  cursor: pointer;
}

.gallery-arrow.prev { left: 12px; }
.gallery-arrow.next { right: 12px; }

.thumbnail-gallery {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.thumbnail {
  width: 82px;
  height: 82px;
  border-radius: 12px;
  object-fit: cover;
  cursor: pointer;
  opacity: .6;
  border: 2px solid transparent;
  background: #0c0c0c;
}

.thumbnail.active {
  opacity: 1;
  border-color: var(--accent);
}

.product-rating {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 14px 0 20px;
}

.rating-star {
  background: none;
  border: none;
  font-size: 22px;
  color: #555;
  cursor: pointer;
  transition: color .2s ease;
}

.rating-star.active,
.rating-star:hover {
  color: var(--accent);
}

.rating-meta {
  font-size: 14px;
  color: var(--text-muted);
  display: flex;
  gap: 6px;
  align-items: baseline;
}

.rating-value {
  font-weight: 700;
  color: var(--text);
}

.product-features-benefits {
  display: grid;
  gap: 22px;
  margin-top: 28px;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}

.product-features-benefits > div {
  background: linear-gradient(180deg, rgba(255, 255, 255, .03), rgba(0, 0, 0, .35));
  border: 1px solid rgba(255, 255, 255, .08);
  border-radius: 16px;
  padding: 18px;
}

.product-features-benefits h4 {
  color: var(--accent);
  margin-bottom: 14px;
}

.product-features-benefits ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 10px;
}

.product-features-benefits li {
  background: rgba(0, 0, 0, .35);
  padding: 10px 12px;
  border-radius: 10px;
}

.subscription-block {
  margin: 18px 0;
  padding: 14px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  background: rgba(0, 0, 0, 0.25);
  display: grid;
  gap: 10px;
}

.subscription-switch {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
}

.subscription-switch input {
  width: 18px;
  height: 18px;
}

.subscription-details {
  display: none;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}

.subscription-details.active {
  display: flex;
}

.subscription-price {
  color: var(--accent);
  font-weight: 800;
}

.cta-btn.secondary {
  background: transparent;
  color: var(--text);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.cta-btn.secondary:hover {
  background: rgba(255, 255, 255, 0.08);
}
</style>
