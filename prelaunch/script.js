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

  const progress = document.getElementById("scroll-progress-fill");
  let progressTicking = false;
  const updateScrollProgress = () => {
    if (!progress || progressTicking) return;
    progressTicking = true;
    window.requestAnimationFrame(() => {
      const scrollable = document.documentElement.scrollHeight - window.innerHeight;
      const ratio = scrollable > 0 ? Math.min(1, Math.max(0, window.scrollY / scrollable)) : 0;
      progress.style.transform = "scaleX(" + ratio + ")";
      progressTicking = false;
    });
  };
  window.addEventListener("scroll", updateScrollProgress, { passive: true });
  window.addEventListener("resize", updateScrollProgress, { passive: true });
  updateScrollProgress();

  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (!prefersReducedMotion && "IntersectionObserver" in window) {
    const revealItems = document.querySelectorAll("[data-reveal], .feature-card, .audience-service, .steps > div, .faq-list details, .trust-items span");
    if (revealItems.length) {
      document.documentElement.classList.add("motion-ready");
      const observer = new IntersectionObserver((entries, currentObserver) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            currentObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12, rootMargin: "0px 0px -28px 0px" });
      revealItems.forEach((item) => observer.observe(item));
    }
  }

  window.addEventListener("pageshow", () => {
    const button = document.querySelector('.signup-form button[type="submit"]');
    const form = document.querySelector(".signup-form");
    if (!button || form?.dataset.submitted === "true") return;
    button.disabled = false;
    button.removeAttribute("aria-busy");
    button.innerHTML = 'Send my request <span aria-hidden="true">→</span>';
  });

  const form = document.querySelector(".signup-form");
  if (form) {
    const status = document.getElementById("form-status");
    const fallback = document.getElementById("form-fallback");
    const submit = form.querySelector('button[type="submit"]');
    const defaultButtonLabel = 'Send my request <span aria-hidden="true">→</span>';
    let submitting = false;
    const setStatus = (kind, message) => {
      if (!status) return;
      status.hidden = false;
      status.className = "form-status is-" + kind;
      status.textContent = message;
    };
    form.addEventListener("invalid", (event) => {
      if (event.target instanceof HTMLElement) {
        event.target.classList.add("is-touched");
        event.target.setAttribute("aria-invalid", "true");
      }
    }, true);
    const updateFieldValidity = (event) => {
      const field = event.target;
      if (!(field instanceof HTMLElement) || !field.matches("input, select, textarea")) return;
      if (field.classList.contains("is-touched")) field.setAttribute("aria-invalid", String(!field.checkValidity()));
    };
    form.addEventListener("input", updateFieldValidity);
    form.addEventListener("change", updateFieldValidity);
    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      if (submitting || !form.reportValidity()) return;
      submitting = true;
      if (submit) { submit.disabled = true; submit.setAttribute("aria-busy", "true"); submit.textContent = "Sending securely…"; }
      if (fallback) fallback.hidden = true;
      setStatus("pending", "Sending your request. Please keep this page open.");
      try {
        const payload = Object.fromEntries(new FormData(form).entries());
        delete payload._next;
        payload._ts = String(Date.now());
        const response = await fetch("https://formsubmit.co/ajax/rosscorelabs@outlook.com", {
          method: "POST",
          headers: { "Content-Type": "application/json", "Accept": "application/json" },
          body: JSON.stringify(payload)
        });
        let result;
        try { result = await response.json(); } catch { throw new Error("unreadable-response"); }
        // The provider may return HTTP 200 even when it refuses a submission.
        if (!response.ok || String(result.success) !== "true") {
          const providerMessage = String(result.message || "");
          if (/activat/i.test(providerMessage)) throw new Error("activation-required");
          throw new Error("submission-rejected");
        }
        form.dataset.submitted = "true";
        form.reset();
        setStatus("success", "Request received by the form service. Thank you for your interest in Zazu EMP.");
        if (fallback) fallback.hidden = false;
        if (submit) { submit.textContent = "Request received ✓"; submit.removeAttribute("aria-busy"); }
        form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach((field) => { field.disabled = true; });
      } catch (error) {
        if (error instanceof Error && error.message === "activation-required") {
          setStatus("error", "The form service still needs its one-time email activation. Please contact us directly using the email link below.");
        } else {
          setStatus("error", "We could not confirm that your request was accepted. It has not been marked as sent. Please retry or email us directly below.");
        }
        if (fallback) fallback.hidden = false;
        submitting = false;
        if (submit) { submit.disabled = false; submit.removeAttribute("aria-busy"); submit.innerHTML = defaultButtonLabel; }
      }
    });
  }
})();