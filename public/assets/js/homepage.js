function vollbild() {
  const container = document.getElementById("kartenContainer");

  if (container.requestFullscreen) {
    container.requestFullscreen();
  } else if (container.webkitRequestFullscreen) {
    container.webkitRequestFullscreen();
  }
}