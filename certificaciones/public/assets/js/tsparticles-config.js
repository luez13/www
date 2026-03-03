document.addEventListener("DOMContentLoaded", function () {
    tsParticles.load("particle-background", {
        background: {
            color: {
                value: "#0B1120" // Un fondo oscuro muy moderno y elegante (azul medianoche)
            }
        },
        fpsLimit: 60,
        interactivity: {
            events: {
                onClick: {
                    enable: true,
                    mode: "push" // Al hacer click agrega más puntos
                },
                onHover: {
                    enable: true,
                    mode: "repulse", // Al pasar el mouse los puntos se apartan
                    parallax: {
                        enable: true,
                        force: 60,
                        smooth: 10
                    }
                },
                resize: true
            },
            modes: {
                push: {
                    quantity: 2 // Reducido para no saturar al hacer click
                },
                repulse: {
                    distance: 100,
                    duration: 0.4
                }
            }
        },
        particles: {
            color: {
                value: "#38bdf8" // Color principal de las partículas (celeste neón/eléctrico)
            },
            links: {
                color: "#38bdf8",
                distance: 150,
                enable: true,
                opacity: 0.4,
                width: 1.5
            },
            collisions: {
                enable: false
            },
            move: {
                direction: "none",
                enable: true,
                outModes: {
                    default: "bounce"
                },
                random: true,
                speed: 1.5,
                straight: false
            },
            number: {
                density: {
                    enable: true,
                    area: 800
                },
                limit: 120, // <-- NUEVO: Límite máximo de partículas en pantalla para proteger el rendimiento
                value: 80 // Cantidad equilibrada inicial
            },
            opacity: {
                value: { min: 0.3, max: 0.7 },
                animation: {
                    enable: true,
                    speed: 1,
                    minimumValue: 0.1
                }
            },
            shape: {
                type: "circle"
            },
            size: {
                value: { min: 1, max: 3 },
                animation: {
                    enable: true,
                    speed: 2,
                    minimumValue: 0.5
                }
            }
        },
        detectRetina: true
    });
});
