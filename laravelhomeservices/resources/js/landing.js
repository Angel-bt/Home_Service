/**
 * Animaciones de la landing "Valle de noche" (solo se carga en layouts/landing).
 *
 * Reglas:
 * - El HTML se ve completo sin JavaScript: aquí solo se anima "desde" un estado, nunca se oculta nada en CSS.
 * - Con prefers-reduced-motion se usa un "modo suave" (fundidos y contadores; sin scroll suave, parallax
 *   ni tarjetas apiladas). Con ?motion=full se fuerza la versión completa.
 * - Los elementos se marcan con atributos data-* en livewire/home-component.blade.php.
 */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import Lenis from 'lenis';

gsap.registerPlugin(ScrollTrigger, SplitText);

const forceFull = new URLSearchParams(window.location.search).get('motion') === 'full';
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches && !forceFull;
if (forceFull) document.documentElement.classList.add('lp-motion-full');

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));
const ease = 'expo.out';

/**
 * Las clases `transition` de Tailwind pelean con GSAP (el elemento puede quedarse en el estado inicial).
 * calm() apaga la transición CSS mientras dura la animación; clearProps la devuelve al terminar.
 */
const calm = (vars) => ({
    ...vars,
    onStart() { this.targets().forEach((el) => { el.style.transition = 'none'; }); vars.onStart?.call(this); },
    clearProps: [vars.clearProps, 'transition'].filter(Boolean).join(','),
});

function smoothScroll() {
    const lenis = new Lenis({ lerp: 0.1, wheelMultiplier: 0.9 });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);

    // Enlaces internos (#oficios…) con el mismo desplazamiento suave.
    $$('a[href*="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const url = new URL(link.href, window.location.href);
            if (url.pathname !== window.location.pathname || !url.hash) return;
            const target = document.querySelector(url.hash);
            if (!target) return;
            event.preventDefault();
            lenis.scrollTo(target, { offset: -90, duration: 1.4 });
            history.replaceState(null, '', url.hash);
        });
    });

    // El modal de acceso bloquea el scroll: pausamos Lenis mientras esté abierto.
    new MutationObserver(() => {
        document.documentElement.style.overflow === 'hidden' ? lenis.stop() : lenis.start();
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['style'] });
}

function hero() {
    const tl = gsap.timeline({ defaults: { ease } });
    const media = $('[data-hero-media]');

    if (media) tl.fromTo(media, { scale: 1.18 }, { scale: 1, duration: 2.4, ease: 'expo.out' }, 0);

    const title = $('[data-hero-title]');
    if (title) {
        const split = SplitText.create(title, { type: 'lines', mask: 'lines' });
        tl.from(split.lines, { yPercent: 110, duration: 1.4, stagger: 0.1 }, 0.2);
    }

    tl.from('[data-hero-eyebrow]', { y: 16, autoAlpha: 0, duration: 1 }, 0.15)
      .from('[data-hero-lede]', { y: 20, autoAlpha: 0, duration: 1.2 }, 0.55)
      .from('[data-hero-search]', { y: 24, autoAlpha: 0, duration: 1.2 }, 0.7)
      .from('[data-hero-services]', { y: 32, autoAlpha: 0, duration: 1.4 }, 0.85)
      .from('[data-hero-zones] > a', calm({ y: 24, autoAlpha: 0, duration: 1, stagger: 0.08, clearProps: 'transform,opacity,visibility' }), 0.9);

    // Al bajar: la foto se mueve más lento y el contenido se desvanece.
    const section = media?.closest('section');
    if (section && media) {
        gsap.to(media, { yPercent: 18, ease: 'none', scrollTrigger: { trigger: section, start: 'top top', end: 'bottom top', scrub: true } });
        gsap.to([title, '[data-hero-lede]', '[data-hero-search]'], {
            y: -60, autoAlpha: 0, ease: 'none', stagger: 0.03, immediateRender: false,
            scrollTrigger: { trigger: section, start: 'top top', end: '70% top', scrub: true },
        });
    }
}

/** Manifiesto: cada palabra se ilumina a medida que se lee. */
function manifesto(gentle = false) {
    const block = $('[data-words]');
    if (!block) return;
    if (gentle) {
        gsap.from(block, { autoAlpha: 0, duration: 1, scrollTrigger: { trigger: block, start: 'top 85%' } });
        return;
    }
    const split = SplitText.create(block, { type: 'words', wordsClass: 'lp-word' });
    gsap.fromTo(split.words, { opacity: 0.14 }, {
        opacity: 1, stagger: 0.05, ease: 'none',
        scrollTrigger: { trigger: block, start: 'top 80%', end: 'bottom 45%', scrub: true },
    });
}

function countUp() {
    $$('[data-count]').forEach((el) => {
        const to = parseInt(el.dataset.count, 10) || 0;
        const counter = { value: 0 };
        gsap.to(counter, {
            value: to, duration: 1.8, ease: 'power2.out',
            onUpdate: () => { el.textContent = Math.round(counter.value); },
            scrollTrigger: { trigger: el, start: 'top 90%', once: true },
        });
    });
}

/** Títulos de sección: líneas que suben desde una máscara al entrar en pantalla. */
function headings() {
    $$('[data-split]').forEach((heading) => {
        SplitText.create(heading, {
            type: 'lines', mask: 'lines', autoSplit: true,
            onSplit: (self) => gsap.from(self.lines, {
                yPercent: 110, duration: 1.3, stagger: 0.08, ease,
                scrollTrigger: { trigger: heading, start: 'top 85%' },
            }),
        });
    });

    ScrollTrigger.batch('[data-anim], [data-row]', {
        start: 'top 90%',
        once: true,
        onEnter: (items) => gsap.from(items, calm({ y: 40, autoAlpha: 0, stagger: 0.07, duration: 1.2, ease, clearProps: 'transform,opacity,visibility' })),
    });
}

/** Oficios: una vista previa del servicio sigue al cursor sobre la lista (solo con ratón). */
function previews() {
    const box = $('[data-preview-box]');
    if (!box || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
    const list = $('[data-row]')?.parentElement;
    if (!list) return;
    const img = $('img', box);
    const xTo = gsap.quickTo(box, 'x', { duration: 0.6, ease: 'power3.out' });
    const yTo = gsap.quickTo(box, 'y', { duration: 0.6, ease: 'power3.out' });
    let current = null;

    const hide = () => {
        if (!current) return;
        current = null;
        gsap.to(box, { autoAlpha: 0, scale: 0.9, duration: 0.25, overwrite: 'auto' });
    };

    // Solo se muestra mientras el cursor está sobre una fila con foto; se decide en cada movimiento,
    // así nunca queda una vista previa "huérfana" si la fila se mueve bajo el cursor.
    list.addEventListener('pointermove', (event) => {
        const row = event.target.closest('[data-row]');
        if (!row || !row.dataset.preview) { hide(); return; }
        xTo(event.clientX + 24);
        yTo(event.clientY - 96);
        if (current !== row) {
            current = row;
            img.src = row.dataset.preview;
            gsap.to(box, { autoAlpha: 1, scale: 1, duration: 0.4, ease: 'power3.out', overwrite: 'auto' });
        }
    });
    list.addEventListener('pointerleave', hide);
    // Al hacer scroll la fila se va de debajo del cursor sin disparar pointerleave: se oculta.
    window.addEventListener('scroll', hide, { passive: true });
    window.addEventListener('blur', hide);
    document.addEventListener('visibilitychange', hide);
    gsap.set(box, { scale: 0.9, autoAlpha: 0 });
}

/** Recomendados: al llegar la siguiente tarjeta, la anterior se reduce y oscurece (solo escritorio). */
function stackedCards() {
    const cards = $$('[data-stack]');
    gsap.matchMedia().add('(min-width: 1024px)', () => {
        cards.forEach((card, i) => {
            const next = cards[i + 1];
            if (!next) return;
            gsap.to(card, {
                scale: 0.92, filter: 'brightness(.55)', ease: 'none',
                scrollTrigger: { trigger: next, start: 'top 65%', end: 'top top+=96', scrub: true },
            });
        });
    });
}

/** Botones "magnéticos": siguen un poco al cursor. Solo con ratón. */
function magnetic() {
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
    $$('[data-magnetic]').forEach((button) => {
        button.style.transitionProperty = 'background-color, color';
        const xTo = gsap.quickTo(button, 'x', { duration: 0.6, ease: 'power3.out' });
        const yTo = gsap.quickTo(button, 'y', { duration: 0.6, ease: 'power3.out' });
        button.addEventListener('pointermove', (event) => {
            const box = button.getBoundingClientRect();
            xTo((event.clientX - box.left - box.width / 2) * 0.2);
            yTo((event.clientY - box.top - box.height / 2) * 0.3);
        });
        button.addEventListener('pointerleave', () => { xTo(0); yTo(0); });
    });
}

/**
 * El encabezado toma el tono de la sección que tiene debajo (data-tone="dark|light" en cada sección),
 * para que el texto siempre contraste. No es una animación: también corre en modo suave.
 */
function headerTone() {
    const header = $('[data-site-header]');
    if (!header) return;
    $$('[data-tone]').filter((el) => el !== header).forEach((section) => {
        ScrollTrigger.create({
            trigger: section,
            start: 'top 36px',
            end: 'bottom 36px',
            onToggle: (self) => { if (self.isActive) header.dataset.tone = section.dataset.tone; },
        });
    });
}

/** Modo suave (prefers-reduced-motion): aparición por opacidad, sin desplazamientos. */
function gentle() {
    gsap.from(['[data-hero-eyebrow]', '[data-hero-title]', '[data-hero-lede]', '[data-hero-search]', '[data-hero-services]', '[data-hero-zones]'], { autoAlpha: 0, duration: 0.8, stagger: 0.08 });
    $$('[data-split], [data-anim], [data-row], [data-stack]').forEach((el) => gsap.from(el, calm({
        autoAlpha: 0, duration: 0.8, clearProps: 'opacity,visibility', scrollTrigger: { trigger: el, start: 'top 92%' },
    })));
    manifesto(true);
    countUp();
}

function init() {
    headerTone();
    if (reduceMotion) {
        gentle();
        return;
    }
    smoothScroll();
    hero();
    manifesto();
    countUp();
    headings();
    previews();
    stackedCards();
    magnetic();
    // Las fuentes cambian la altura de los títulos: recalcular posiciones cuando terminen de cargar.
    document.fonts?.ready.then(() => ScrollTrigger.refresh());
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
