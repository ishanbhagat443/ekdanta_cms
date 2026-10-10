// Ekdanta section pack: the hand-coded scenes, registered with the motion
// engine (imported here too, so this file never runs before it).
import './motion/index.js';
import torchBeam from './sections/ekdanta/torch-beam.js';

window.EkMotion.register('ekdanta-home-hero', torchBeam);
