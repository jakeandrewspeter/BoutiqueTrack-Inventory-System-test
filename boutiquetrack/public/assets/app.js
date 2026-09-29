document.addEventListener("click", (event) => {
  const dismiss = event.target.closest("[data-dismiss]");
  if (dismiss) dismiss.closest(".notice")?.remove();
});

document.addEventListener("submit", (event) => {
  const form = event.target.closest("form[data-confirm]");
  if (form && !window.confirm(form.dataset.confirm || "Continue?")) {
    event.preventDefault();
  }
});

const discountInput = document.querySelector("#discount");
if (discountInput) {
  const amountDue = document.querySelector(".grand-total strong");
  const subtotalText = document.querySelector(".total-row:not(.grand-total) strong")?.textContent ?? "";
  const subtotal = Number(subtotalText.replace(/[^\d.-]/g, "")) || 0;
  const currency = subtotalText.match(/^[^\d]*/)?.[0] ?? "";
  discountInput.addEventListener("input", () => {
    const discount = Math.min(Math.max(Number(discountInput.value) || 0, 0), subtotal);
    if (amountDue) amountDue.textContent = `${currency}${(subtotal - discount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  });
}