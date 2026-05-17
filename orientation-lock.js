(function () {
  const isTouchDevice = matchMedia("(pointer: coarse)").matches;
  const isSmallScreen = Math.min(innerWidth, innerHeight) <= 1024;

  if (!isTouchDevice && !isSmallScreen) return;

  const style = document.createElement("style");
  style.textContent = `
    #rotateAdvice {
      position: fixed;
      top: max(10px, env(safe-area-inset-top));
      left: 12px;
      right: 12px;
      z-index: 99999;
      display: none;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 9px 12px;
      border: 1px solid rgba(255,255,255,.18);
      border-radius: 8px;
      background: rgba(7, 18, 31, .82);
      color: #f8fafc;
      font-family: "Segoe UI", Arial, sans-serif;
      font-size: 13px;
      line-height: 1.3;
      text-align: center;
      box-shadow: 0 12px 30px rgba(0,0,0,.25);
      backdrop-filter: blur(12px);
      pointer-events: none;
    }

    #rotateAdvice.is-visible {
      display: flex;
    }

    #rotateAdvice span {
      color: #cbd5e1;
    }
  `;

  const advice = document.createElement("div");
  advice.id = "rotateAdvice";
  advice.innerHTML = `<strong>Tip:</strong> <span>Rotate your phone for a wider view.</span>`;

  let hideTimer = null;

  function isPortrait() {
    return innerHeight > innerWidth;
  }

  function showAdvice() {
    if (!isPortrait()) {
      advice.classList.remove("is-visible");
      return;
    }

    advice.classList.add("is-visible");
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => advice.classList.remove("is-visible"), 6500);
  }

  document.addEventListener("DOMContentLoaded", () => {
    document.head.appendChild(style);
    document.body.appendChild(advice);
    showAdvice();
  });

  addEventListener("resize", showAdvice);
  addEventListener("orientationchange", () => setTimeout(showAdvice, 250));
})();
