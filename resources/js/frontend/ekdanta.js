// Ekdanta section pack: the hand-coded scenes, registered with the motion
// engine (imported here too, so this file never runs before it).
import './motion/index.js';
import torchBeam from './sections/ekdanta/torch-beam.js';
import workIntro from './sections/ekdanta/work-intro.js';
import indexCarousel from './sections/ekdanta/index-carousel.js';

window.EkMotion.register('ekdanta-home-hero', torchBeam);
window.EkMotion.register('ekdanta-work-showcase', workIntro);
window.EkMotion.register('ekdanta-reels-carousel', indexCarousel);
