(function () {
  const isTouchDevice = matchMedia("(pointer: coarse)").matches;
  const isSmallScreen = Math.min(innerWidth, innerHeight) <= 1024;
  const shouldRequireLandscape = isTouchDevice || isSmallScreen;

  if (!shouldRequireLandscape) return;

  const style = document.createElement("style");
  style.textContent = `
    #landscapeLockOverlay {
      position: fixed;
      inset: 0;
      z-index: 99999;
      display: none;
      place-items: center;
      padding: 28px;
      background: #07111d;
      color: #f8fafc;
      font-family: "Segoe UI", Arial, sans-serif;
      text-align: center;
    }

    #landscapeLockOverlay.is-visible {
      display: grid;
    }

    #landscapeLockOverlay .panel {
      max-width: 420px;
    }

    #landscapeLockOverlay .phone {
      width: 78px;
      height: 124px;
      margin: 0 auto 22px;
      border: 4px solid #f8fafc;
      border-radius: 16px;
      transform: rotate(90deg);
      box-shadow: 0 0 0 1px rgba(255,255,255,.18);
    }

    #landscapeLockOverlay h2 {
      margin: 0 0 10px;
      font-size: 26px;
      line-height: 1.15;
      letter-spacing: 0;
    }

    #landscapeLockOverlay p {
      margin: 0;
      color: #cbd5e1;
      line-height: 1.45;
      font-size: 15px;
    }

    #landscapeLockOverlay button {
      margin-top: 18px;
      border: 0;
      border-radius: 8px;
      padding: 11px 16px;
      background: #f97316;
      color: #111827;
      font-weight: 800;
      cursor: pointer;
    }
  `;

  const overlay = document.createElement("div");
  overlay.id = "landscapeLockOverlay";
  overlay.innerHTML = `
    <div class="panel">
      <div class="phone" aria-hidden="true"></div>
      <h2>Rotate to Landscape</h2>
      <p>This campus guide is designed for phone and tablet landscape view.</p>
      <button type="button">Continue in Landscape</button>
    </div>
  `;

  function isPortrait() {
    return innerHeight > innerWidth;
  }

  async function requestLandscape() {
    try {
      if (screen.orientation?.lock) {
        await screen.orientation.lock("landscape");
      }
    } catch (_) {
      // Some mobile browsers only allow orientation lock after fullscreen or user action.
    }
  }

  async function requestFullscreenThenLandscape() {
    try {
      if (!document.fullscreenElement && document.documentElement.requestFullscreen) {
        await document.documentElement.requestFullscreen();
      }
    } catch (_) {
      // iOS Safari and some embedded browsers do not allow programmatic fullscreen.
    }

    await requestLandscape();
    updateOverlay();
  }

  function updateOverlay() {
    overlay.classList.toggle("is-visible", isPortrait());
  }

  document.addEventListener("DOMContentLoaded", () => {
    document.head.appendChild(style);
    document.body.appendChild(overlay);
    overlay.querySelector("button").addEventListener("click", requestFullscreenThenLandscape);
    requestLandscape();
    updateOverlay();
  });

  addEventListener("resize", updateOverlay);
  addEventListener("orientationchange", () => setTimeout(updateOverlay, 250));
  addEventListener("pointerdown", requestLandscape, { once: true });
})();
