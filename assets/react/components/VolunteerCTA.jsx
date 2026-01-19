import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Users } from 'lucide-react';

const VolunteerCTA = ({
  backgroundImage = '/assets/images/volunteer-office.jpg',
  title = 'Join Our Movement',
  subtitle = 'Every volunteer makes a difference',
  onSubmit
}) => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    zip: ''
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    if (onSubmit) {
      onSubmit(formData);
    }
    // Reset form
    setFormData({ name: '', email: '', phone: '', zip: '' });
  };

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  return (
    <section className="relative bg-navy py-20 md:py-28 overflow-hidden">
      {/* Background Pattern */}
      <div
        className="absolute inset-0 opacity-10 bg-cover bg-center"
        style={{ backgroundImage: `url(${backgroundImage})` }}
      ></div>

      <div className="container mx-auto px-6 max-w-4xl relative z-10">
        {/* Header */}
        <div className="text-center mb-12">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="flex justify-center mb-6"
          >
            <Users className="text-gold" size={64} strokeWidth={1.5} />
          </motion.div>
          <motion.h2
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1 }}
            className="font-display text-white text-4xl md:text-5xl lg:text-6xl font-bold mb-4"
          >
            {title}
          </motion.h2>
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="font-sans text-white/90 text-lg md:text-xl"
          >
            {subtitle}
          </motion.p>
        </div>

        {/* Form */}
        <motion.form
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.3 }}
          onSubmit={handleSubmit}
          className="bg-white p-8 md:p-10"
          data-testid="volunteer-form"
        >
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            {/* Name */}
            <div>
              <label htmlFor="volunteer-name" className="block font-sans text-navy text-sm font-semibold uppercase tracking-wider mb-2">
                Full Name *
              </label>
              <div className="relative">
                <input
                  type="text"
                  id="volunteer-name"
                  name="name"
                  value={formData.name}
                  onChange={handleChange}
                  required
                  data-testid="volunteer-name"
                  className="w-full border-2 border-neutral-300 px-4 py-3 font-sans text-neutral-900 focus:border-gold focus:outline-none transition-colors"
                  placeholder="John Smith"
                />
              </div>
            </div>

            {/* Email */}
            <div>
              <label htmlFor="volunteer-email" className="block font-sans text-navy text-sm font-semibold uppercase tracking-wider mb-2">
                Email Address *
              </label>
              <div className="relative">
                <input
                  type="email"
                  id="volunteer-email"
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                  required
                  data-testid="volunteer-email"
                  className="w-full border-2 border-neutral-300 px-4 py-3 font-sans text-neutral-900 focus:border-gold focus:outline-none transition-colors"
                  placeholder="john@example.com"
                />
              </div>
            </div>

            {/* Phone */}
            <div>
              <label htmlFor="volunteer-phone" className="block font-sans text-navy text-sm font-semibold uppercase tracking-wider mb-2">
                Phone Number
              </label>
              <div className="relative">
                <input
                  type="tel"
                  id="volunteer-phone"
                  name="phone"
                  value={formData.phone}
                  onChange={handleChange}
                  data-testid="volunteer-phone"
                  className="w-full border-2 border-neutral-300 px-4 py-3 font-sans text-neutral-900 focus:border-gold focus:outline-none transition-colors"
                  placeholder="(555) 123-4567"
                />
              </div>
            </div>

            {/* ZIP Code */}
            <div>
              <label htmlFor="volunteer-zip" className="block font-sans text-navy text-sm font-semibold uppercase tracking-wider mb-2">
                ZIP Code *
              </label>
              <div className="relative">
                <input
                  type="text"
                  id="volunteer-zip"
                  name="zip"
                  value={formData.zip}
                  onChange={handleChange}
                  required
                  data-testid="volunteer-zip"
                  className="w-full border-2 border-neutral-300 px-4 py-3 font-sans text-neutral-900 focus:border-gold focus:outline-none transition-colors"
                  placeholder="12345"
                />
              </div>
            </div>
          </div>

          {/* Submit Button */}
          <button
            type="submit"
            data-testid="volunteer-submit"
            className="w-full bg-red text-white px-12 py-4 font-sans font-semibold text-base tracking-[0.15em] uppercase hover:bg-red-800 transition-colors duration-200"
          >
            Sign Up to Volunteer
          </button>

          <p className="text-neutral-600 text-sm font-sans text-center mt-6">
            By signing up, you agree to receive updates from the campaign.
          </p>
        </motion.form>
      </div>
    </section>
  );
};

export default VolunteerCTA;
