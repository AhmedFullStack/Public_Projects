import "https://cdn.jsdelivr.net/npm/motion@12.23.24/+esm"
    
// Progress bar representing gallery scroll
scroll(animate(".progress", { scaleX: [0, 1] }, { ease: "linear" }))

document.querySelectorAll(".img-container").forEach((section) => {
    const header = section.querySelector("h2")
    scroll(animate(header, { y: [-400, 400] }, { ease: "linear" }), {
        target: header,
    })
})