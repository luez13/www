function addModuleFields() {
    const number = document.getElementById("numero_modulos").value;
    const container = document.getElementById("moduleContainer");
    container.innerHTML = "";
    for (let i = 0; i < number; i++) {
        const moduleDiv = createModuleDiv(i);
        container.appendChild(moduleDiv);
    }
}

function createModuleDiv(index) {
    const moduleDiv = document.createElement("div");
    moduleDiv.className = "module mb-4 p-3 border"; // Using Bootstrap classes for margin, padding, and border
    moduleDiv.appendChild(createElement("h4", "Módulo " + (index + 1)));
    moduleDiv.appendChild(createInput("text", "nombre_modulo[]", "Nombre del módulo"));
    const containerContenido = createContainerContenido();
    moduleDiv.appendChild(containerContenido);
    const buttonAgregarContenido = createButton("Agregar contenido", function() {
        agregarContenido(containerContenido);
    });
    moduleDiv.appendChild(buttonAgregarContenido);
    moduleDiv.appendChild(createInput("text", "actividad[]", "Actividad"));
    moduleDiv.appendChild(createInput("text", "instrumento[]", "Instrumento"));
    return moduleDiv;
}

function createContainerContenido() {
    const containerContenido = document.createElement("div");
    containerContenido.className = "container-contenido mb-3"; // Using Bootstrap for spacing
    containerContenido.appendChild(createTextarea("contenido[]", "Contenido"));
    return containerContenido;
}

function createElement(tag, textContent) {
    const element = document.createElement(tag);
    element.textContent = textContent;
    return element;
}

function createInput(type, name, placeholder) {
    const input = document.createElement("input");
    input.type = type;
    input.name = name;
    input.placeholder = placeholder;
    input.required = true;
    input.className = "form-control mb-2"; // Using Bootstrap for form control and spacing
    return input;
}

function createTextarea(name, placeholder) {
    const textarea = document.createElement("textarea");
    textarea.name = name;
    textarea.placeholder = placeholder;
    textarea.required = true;
    textarea.className = "form-control mb-2"; // Using Bootstrap for form control and spacing
    return textarea;
}

function createButton(textContent, onClickHandler) {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = textContent;
    button.onclick = onClickHandler;
    button.className = "btn btn-secondary mb-2"; // Using Bootstrap for button styling
    return button;
}

function agregarContenido(containerContenido) {
    const newTextArea = createTextarea("contenido[]", "Contenido");
    containerContenido.appendChild(newTextArea);
    const buttonQuitarContenido = createButton("Quitar contenido", function() {
        containerContenido.removeChild(newTextArea);
        containerContenido.removeChild(buttonQuitarContenido);
    });
    containerContenido.appendChild(buttonQuitarContenido);
}

document.getElementById('numero_modulos').addEventListener('blur', addModuleFields);

function combineContentsBeforeSubmit() {
    var modules = document.getElementsByClassName('module');
    var logData = [];
    for (var i = 0; i < modules.length; i++) {
        var containerContenido = modules[i].getElementsByClassName('container-contenido')[0];
        var textareas = containerContenido.getElementsByTagName('textarea');
        var combinedContent = '';
        for (var j = 0; j < textareas.length; j++) {
            combinedContent += '[' + textareas[j].value + ']';
            logData.push(textareas[j].value);  // Agrega cada contenido para el log
        }
        if (textareas.length > 0) {
            textareas[0].value = combinedContent;
            while (textareas.length > 1) {
                containerContenido.removeChild(textareas[1]);
            }
        }
    }

    // Enviar los datos al servidor para que los escriba en un archivo
    fetch('save_logs.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(logData)
    })
    .then(response => response.text())
    .then(result => console.log(result))
    .catch(error => console.log('Error:', error));
}

document.getElementById('crearCursoForm').onsubmit = function(e) {
    e.preventDefault();
    combineContentsBeforeSubmit();
    var formData = new FormData(this);
    console.log('Form Data:', ...formData.entries()); // Esto imprimirá todos los datos del formulario, incluyendo el contenido combinado

    $.ajax({
        url: '../controllers/curso_controlador.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            alert(response);
            window.location.href = '../public/perfil.php?seccion=ver_postulaciones';
        },
        error: function() {
            alert('Error al crear el curso.');
        }
    });
};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('button.add-contenido').forEach(button => {
        button.addEventListener('click', function() {
            const moduleContainer = this.closest('.module-container');
            const newContent = document.createElement('textarea');
            newContent.className = 'form-control';
            newContent.name = 'contenido[]';
            newContent.required = true;
            moduleContainer.querySelector('.contenidos').appendChild(newContent);
        });
    });

    document.querySelectorAll('button.remove-contenido').forEach(button => {
        button.addEventListener('click', function() {
            const moduleContainer = this.closest('.module-container');
            if (moduleContainer.querySelectorAll('textarea').length > 1) {
                moduleContainer.querySelector('.contenidos').lastChild.remove();
            }
        });
    });
});
