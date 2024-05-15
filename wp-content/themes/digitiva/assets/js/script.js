document.addEventListener("DOMContentLoaded", function () {
  // Wait until images, links, fonts, stylesheets, and js is loaded
  window.addEventListener("load",
    function () {
      /* ================= > Front Page - Animations < ================= */
      if (document.querySelector('.header-columns')) {
        const animationObject = document.querySelector('.header-columns');
        gsap.from(animationObject.querySelectorAll('div'), {
          scrollTrigger: {
            trigger: animationObject,
            start: 'top 80%',
            toggleActions: "play none none none",
          },
          autoAlpha: 0,
          opacity: 0,
          duration: 0.7
        });
      }

      if (document.querySelector('.home-title-with-description-group')) {
        const animationObject = document.querySelector('.home-title-with-description-group');
        gsap.from(animationObject.querySelectorAll('div'), {
          scrollTrigger: {
            trigger: animationObject,
            start: 'top 80%',
            toggleActions: "play none none none",
          },
          autoAlpha: 0,
          opacity: 0,
          y: -50,
          duration: 0.7,
          stagger: 0.075,
        });
      }

      if (document.querySelector('.brands-group')) {
        const animationObject = document.querySelector('.brands-group');
        gsap.from(animationObject.querySelectorAll('div, img'), {
          scrollTrigger: {
            trigger: animationObject,
            start: 'top 80%',
            toggleActions: "play none none none",
          },
          autoAlpha: 0,
          opacity: 0,
          scale: 0,
          // y: 50,
          duration: 1.2,
          stagger: 0.075,
        });
      }

      if (document.querySelector('.who-are-we-group')) {
        const animationObject = document.querySelector('.who-are-we-group');
        gsap.from(animationObject.querySelectorAll('div'), {
          scrollTrigger: {
            trigger: animationObject,
            start: 'top 80%',
            toggleActions: "play none none none",
          },
          autoAlpha: 0,
          opacity: 0,
          y: -50,
          duration: 0.7,
          stagger: 0.075,
        });
      }

      if (document.querySelector('.our-services-group')) {
        const animationObject = document.querySelector('.our-services-group');
        gsap.from(animationObject.querySelectorAll('div'), {
          scrollTrigger: {
            trigger: animationObject,
            start: 'top 80%',
            toggleActions: "play none none none",
          },
          autoAlpha: 0,
          opacity: 0,
          x: -50,
          duration: 0.7,
          stagger: 0.075,
        });
      }
    })
});