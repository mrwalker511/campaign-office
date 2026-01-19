import React, { useState } from 'react';
import { Facebook, Twitter, Instagram, Youtube } from 'lucide-react';

const Footer = ({
  logo = 'ANDERSON 2026',
  tagline = 'Restoring Faith In America',
  navLinks = [
    { label: 'About', href: '#about' },
    { label: 'Issues', href: '#issues' },
    { label: 'News', href: '#news' },
    { label: 'Events', href: '#events' },
    { label: 'Volunteer', href: '#volunteer' },
    { label: 'Donate', href: '#donate' },
    { label: 'Contact', href: '#contact' },
    { label: 'Privacy Policy', href: '#privacy' }
  ],
  socialLinks = {
    facebook: 'https://facebook.com',
    twitter: 'https://twitter.com',
    instagram: 'https://instagram.com',
    youtube: 'https://youtube.com'
  },
  disclaimer = 'Paid for by Anderson for Senate. Not authorized by any candidate or candidate\'s committee.',
  onEmailSignup
}) => {
  const [email, setEmail] = useState('');

  const handleEmailSubmit = (e) => {
    e.preventDefault();
    if (onEmailSignup) {
      onEmailSignup(email);
    }
    setEmail('');
  };

  return (
    <footer className="bg-navy text-white">
      {/* Main Footer Content */}
      <div className="container mx-auto px-6 py-16 md:py-20">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8">
          {/* Logo and Tagline */}
          <div className="lg:col-span-4">
            <h3 className="font-display text-white text-3xl md:text-4xl font-bold mb-3">
              {logo}
            </h3>
            <p className="text-gold text-sm uppercase tracking-[0.2em] font-sans font-semibold mb-8">
              {tagline}
            </p>
            <p className="font-sans text-white/80 text-base leading-relaxed">
              Join us in our mission to restore faith in America and fight for the values that made this country great.
            </p>
          </div>

          {/* Navigation Links */}
          <div className="lg:col-span-4">
            <h4 className="font-sans text-white text-sm uppercase tracking-[0.2em] font-bold mb-6 border-b-2 border-gold pb-3">
              Quick Links
            </h4>
            <nav>
              <ul className="grid grid-cols-2 gap-x-4 gap-y-3">
                {navLinks.map((link, index) => (
                  <li key={index}>
                    <a
                      href={link.href}
                      data-testid={`footer-link-${link.label.toLowerCase().replace(/\s+/g, '-')}`}
                      className="font-sans text-white/80 text-base hover:text-gold transition-colors duration-200"
                    >
                      {link.label}
                    </a>
                  </li>
                ))}
              </ul>
            </nav>
          </div>

          {/* Email Signup */}
          <div className="lg:col-span-4">
            <h4 className="font-sans text-white text-sm uppercase tracking-[0.2em] font-bold mb-6 border-b-2 border-gold pb-3">
              Stay Informed
            </h4>
            <p className="font-sans text-white/80 text-base mb-6">
              Get campaign updates and important news delivered to your inbox.
            </p>
            <form onSubmit={handleEmailSubmit} data-testid="footer-email-form">
              <div className="flex flex-col sm:flex-row gap-3">
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                  data-testid="footer-email-input"
                  placeholder="Your email address"
                  className="flex-1 bg-white/10 border border-white/30 px-4 py-3 text-white placeholder-white/50 focus:border-gold focus:outline-none transition-colors font-sans"
                />
                <button
                  type="submit"
                  data-testid="footer-email-submit"
                  className="bg-gold text-navy px-6 py-3 font-sans font-semibold uppercase tracking-wider hover:bg-gold-700 transition-colors duration-200 whitespace-nowrap"
                >
                  Sign Up
                </button>
              </div>
            </form>
          </div>
        </div>

        {/* Social Links */}
        <div className="mt-12 pt-8 border-t border-white/20">
          <div className="flex flex-col md:flex-row items-center justify-between gap-6">
            <div className="flex items-center gap-6">
              <span className="font-sans text-white/80 text-sm uppercase tracking-wider">
                Follow Us:
              </span>
              <div className="flex items-center gap-4">
                <a
                  href={socialLinks.facebook}
                  target="_blank"
                  rel="noopener noreferrer"
                  data-testid="social-facebook"
                  className="text-white/80 hover:text-gold transition-colors duration-200"
                  aria-label="Facebook"
                >
                  <Facebook size={24} />
                </a>
                <a
                  href={socialLinks.twitter}
                  target="_blank"
                  rel="noopener noreferrer"
                  data-testid="social-twitter"
                  className="text-white/80 hover:text-gold transition-colors duration-200"
                  aria-label="Twitter"
                >
                  <Twitter size={24} />
                </a>
                <a
                  href={socialLinks.instagram}
                  target="_blank"
                  rel="noopener noreferrer"
                  data-testid="social-instagram"
                  className="text-white/80 hover:text-gold transition-colors duration-200"
                  aria-label="Instagram"
                >
                  <Instagram size={24} />
                </a>
                <a
                  href={socialLinks.youtube}
                  target="_blank"
                  rel="noopener noreferrer"
                  data-testid="social-youtube"
                  className="text-white/80 hover:text-gold transition-colors duration-200"
                  aria-label="YouTube"
                >
                  <Youtube size={24} />
                </a>
              </div>
            </div>

            {/* Copyright and Disclaimer */}
            <div className="text-center md:text-right">
              <p className="font-sans text-white/60 text-sm">
                &copy; {new Date().getFullYear()} Anderson for Senate. All rights reserved.
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Legal Disclaimer */}
      <div className="bg-navy-900 py-6">
        <div className="container mx-auto px-6">
          <p className="font-sans text-white/50 text-xs text-center leading-relaxed">
            {disclaimer}
          </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
