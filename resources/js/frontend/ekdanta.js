// Ekdanta section pack: the hand-coded scenes, registered with the motion
// engine (imported here too, so this file never runs before it).
import './motion/index.js';
import torchBeam from './sections/ekdanta/torch-beam.js';
import workIntro from './sections/ekdanta/work-intro.js';
import indexCarousel from './sections/ekdanta/index-carousel.js';
import scatterToSlots from './sections/ekdanta/scatter-to-slots.js';
import initBriefBuilders from './sections/ekdanta/brief-builder.js';
import serviceDeepDive from './sections/ekdanta/service-deep-dive.js';
import initPageFinders from './sections/ekdanta/page-finder.js';
import aboutStory from './sections/ekdanta/about-story.js';

window.EkMotion.register('ekdanta-home-hero', torchBeam);
window.EkMotion.register('ekdanta-work-showcase', workIntro);
window.EkMotion.register('ekdanta-reels-carousel', indexCarousel);
window.EkMotion.register('ekdanta-brand-shelves', scatterToSlots);
window.EkMotion.register('ekdanta-service-deep-dive', serviceDeepDive);
window.EkMotion.register('ekdanta-our-story', aboutStory);

initBriefBuilders();
initPageFinders();

// "Received today at 3:42 pm": the visitor's own clock ([data-ek-now] holds "… {time} …").
document.querySelectorAll('[data-ek-now]').forEach((el) => {
    try {
        const time = new Date().toLocaleTimeString('en-IN', { hour: 'numeric', minute: '2-digit', hour12: true }).toLowerCase();
        el.textContent = el.dataset.ekNow.replace('{time}', time);
    } catch {
        // keep the fallback ("just now")
    }
});
