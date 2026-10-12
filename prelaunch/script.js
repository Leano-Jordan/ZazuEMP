(() => {
  "use strict";
  const year = document.getElementById("year");
  if (year) year.textContent = String(new Date().getFullYear());

  const interest = document.getElementById("interest");
  document.querySelectorAll("[data-intent]").forEach((link) => {
    link.addEventListener("click", () => {
      if (!interest) return;
      const intent = link.getAttribute("data-intent");
      const option = Array.from(interest.options).find((item) => item.value === intent);
      if (option) interest.value = option.value;
    });
  });

  const form = document.querySelector(".signup-form");
  if (form) {
    form.addEventListener("submit", (event) => {
      if (!form.reportValidity()) {
        event.preventDefault();
        return;
      }
      const submit = form.querySelector('button[type="submit"]');
      if (submit) {
        submit.disabled = true;
        submit.setAttribute("aria-busy", "true");
        submit.textContent = "Sending request…";
      }
    });
  }
})();