import React from 'react';
import { motion } from 'framer-motion';
import { Play } from 'lucide-react';

const HeroSection = ({
  backgroundImage = '/assets/images/rally-background.jpg',
  logo = 'Campaign 2026',
  tagline = 'For Senate \'26',
  headline = 'Restoring Faith In America',
  primaryCTA = 'Join the Movement',
  secondaryCTA = 'Watch the Video',
  onPrimaryClick,
  onSecondaryClick
}) => {
  return (
    <section className="relative h-screen flex items-center justify-center overflow-hidden">
      {/* Background Image with Dark Overlay */}
      <div
        className="absolute inset-0 bg-cover bg-center"
        style={{ backgroundImage: `url(${backgroundImage})` }}
      >
        <div className="absolute inset-0 bg-gradient-to-b from-navy-900/95 to-navy-800/95"></div>
      </div>

      {/* Content */}
      <div className="relative z-10 text-center px-6 max-w-5xl mx-auto">
        {/* Logo and Tagline */}
        <motion.div
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8 }}
        >
          <h1 className="font-display text-white text-2xl md:text-3xl mb-2 tracking-wider">
            {logo}
          </h1>
          <p className="text-gold uppercase text-sm md:text-base font-sans tracking-[0.3em] font-semibold mb-8">
            {tagline}
          </p>
        </motion.div>

        {/* Main Headline */}
        <motion.h2
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.3 }}
          className="font-display text-white text-5xl md:text-7xl lg:text-8xl font-bold mb-12 leading-tight"
        >
          {headline}
        </motion.h2>

        {/* CTAs */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.6 }}
          className="flex flex-col sm:flex-row gap-6 justify-center items-center"
        >
          {/* Primary CTA */}
          <button
            onClick={onPrimaryClick}
            data-testid="hero-primary-cta"
            className="bg-red text-white px-12 py-4 font-sans font-semibold text-base tracking-[0.15em] uppercase hover:bg-red-800 transition-colors duration-200"
          >
            {primaryCTA}
          </button>

          {/* Secondary CTA */}
          <button
            onClick={onSecondaryClick}
            data-testid="hero-secondary-cta"
            className="border-2 border-white text-white px-12 py-4 font-sans font-semibold text-base tracking-[0.15em] uppercase hover:bg-white hover:text-navy transition-all duration-200 flex items-center gap-3"
          >
            <Play size={20} fill="currentColor" />
            {secondaryCTA}
          </button>
        </motion.div>
      </div>

      {/* Scroll Indicator */}
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 1, delay: 1.2 }}
        className="absolute bottom-10 left-1/2 transform -translate-x-1/2"
      >
        <motion.div
          animate={{ y: [0, 10, 0] }}
          transition={{ duration: 1.5, repeat: Infinity }}
          className="w-6 h-10 border-2 border-white/50 rounded-full flex items-start justify-center p-2"
        >
          <div className="w-1 h-3 bg-white/50 rounded-full"></div>
        </motion.div>
      </motion.div>
    </section>
  );
};

export default HeroSection;
