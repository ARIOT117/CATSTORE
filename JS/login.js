const formulario = document.getElementById("login-formulario");

formulario.addEventListener("submit", (event) => {
    event.preventDefault();
    alert("Inicio de sesión exitoso. Bienvenido a CatStore! sientete comodo de explorar nuestra tienda "+ document.getElementById("nombres").value );
});
