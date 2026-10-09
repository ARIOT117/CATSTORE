const formulario = document.getElementById("login-formulario");
const resultado = document.getElementById("login-resultado");

formulario.addEventListener("submit", (event) => {
    event.preventDefault();
    const nombre = document.getElementById("nombres").value.trim();
    resultado.textContent = `Formulario de demostración completado. ¡Bienvenido a CAOTITOS, ${nombre}! No se ha creado una cuenta.`;
    formulario.elements.contrasena.value = "";
});
