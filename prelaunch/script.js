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

  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (!prefersReducedMotion && "IntersectionObserver" in window) {
    const revealItems = document.querySelectorAll("[data-reveal]");
    if (revealItems.length) {
      document.documentElement.classList.add("motion-ready");
      const observer = new IntersectionObserver((entries, currentObserver) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            currentObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12, rootMargin: "0px 0px -35px 0px" });
      revealItems.forEach((item) => observer.observe(item));
    }
  }

  window.addEventListener("pageshow", () => {
    const button = document.querySelector('.signup-form button[type="submit"]');
    if (!button) return;
    button.disabled = false;
    button.removeAttribute("aria-busy");
    button.innerHTML = 'Send my request <span aria-hidden="true">→</span>';
  });

  const form = document.querySelector(".signup-form");
  if (form) {
    form.addEventListener("invalid", (event) => {
      if (event.target instanceof HTMLElement) {
        event.target.classList.add("is-touched");
        event.target.setAttribute("aria-invalid", "true");
      }
    }, true);

    const updateFieldValidity = (event) => {
      const field = event.target;
      if (!(field instanceof HTMLElement) || !field.matches("input, select, textarea")) return;
      if (field.classList.contains("is-touched")) {
        field.setAttribute("aria-invalid", String(!field.checkValidity()));
      }
    };
    form.addEventListener("input", updateFieldValidity);
    form.addEventListener("change", updateFieldValidity);

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