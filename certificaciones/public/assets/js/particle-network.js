let particles = [];

function setup() {
    let canvas = createCanvas(windowWidth, windowHeight);
    canvas.parent('particle-background');
    canvas.style('position', 'absolute');
    canvas.style('top', '0');
    canvas.style('left', '0');
    canvas.style('z-index', '-1'); // Asegura que esté detrás del contenido
    for (let i = 0; i < 50; i++) {
        particles.push(new Particle());
    }
}

function draw() {
    background('#6c757d'); // Color de fondo gris
    for (let i = particles.length - 1; i >= 0; i--) {
        let particle = particles[i];
        particle.update();
        particle.display();
        particle.connect(particles);

        // Eliminar partículas que salen de la pantalla y generar nuevas desde abajo
        if (particle.pos.y < 0) {
            particles.splice(i, 1);
            particles.push(new Particle(true)); // Añadir una nueva partícula desde abajo
        }
    }
}

function windowResized() {
    resizeCanvas(windowWidth, windowHeight);
}

class Particle {
    constructor(newParticle = false) {
        this.pos = createVector(random(width), newParticle ? height : random(height)); // Generar desde el borde inferior si es nueva
        this.vel = createVector(random(-0.25, 0.25), -0.5); // Ajustar la velocidad a 0.5
    }

    update() {
        this.pos.add(this.vel);
        if (this.pos.x > width || this.pos.x < 0) this.vel.x *= -1;
    }

    display() {
        stroke(220); // Esferas ligeramente menos blancas que #FFFFFF
        strokeWeight(6); // Tamaño más grande de los puntos
        point(this.pos.x, this.pos.y);
    }

    connect(particles) {
        for (let particle of particles) {
            let d = dist(this.pos.x, this.pos.y, particle.pos.x, particle.pos.y);
            if (d > 10 && d < 200) {
                stroke(220, 150);
                strokeWeight(1); // Líneas más finas
                line(this.pos.x, this.pos.y, particle.pos.x, particle.pos.y);
            }
        }
    }
}