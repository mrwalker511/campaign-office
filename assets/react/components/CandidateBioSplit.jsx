import React from 'react';
import { motion } from 'framer-motion';
import { Quote } from 'lucide-react';

const CandidateBioSplit = ({
  image = '/assets/images/candidate-portrait.jpg',
  name = 'John Anderson',
  title = 'Candidate for U.S. Senate',
  bio = [
    'A fifth-generation American with deep roots in our community, John Anderson has spent the last 25 years fighting for the values that made this country great.',
    'As a former Marine, small business owner, and state legislator, John understands the challenges facing everyday Americans. He\'s not a career politician—he\'s a proven leader who gets results.',
    'John and his wife Sarah have raised three children here, coached Little League, and volunteered at local charities. He knows what matters to families because he lives it every day.'
  ],
  quote = 'I\'m running for Senate because our country needs leaders who will put principle over politics and people over party.',
  quoteAttribution = 'John Anderson'
}) => {
  return (
    <section className="bg-white py-20 md:py-28">
      <div className="container mx-auto px-6 max-w-7xl">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
          {/* Left: Image */}
          <motion.div
            initial={{ opacity: 0, x: -30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
            className="relative"
          >
            <div className="relative">
              <img
                src={image}
                alt={name}
                className="w-full h-auto shadow-2xl"
                data-testid="candidate-image"
              />
              {/* Gold accent border */}
              <div className="absolute -bottom-6 -right-6 w-full h-full border-4 border-gold -z-10"></div>
            </div>
          </motion.div>

          {/* Right: Biography */}
          <motion.div
            initial={{ opacity: 0, x: 30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
          >
            <p className="text-gold uppercase text-sm font-sans font-semibold tracking-[0.3em] mb-4">
              Meet the Candidate
            </p>
            <h2 className="font-display text-navy text-4xl md:text-5xl font-bold mb-3">
              {name}
            </h2>
            <p className="text-neutral-600 text-lg font-sans font-medium mb-8">
              {title}
            </p>

            {/* Bio Paragraphs */}
            <div className="space-y-6 mb-10">
              {bio.map((paragraph, index) => (
                <p
                  key={index}
                  className="font-sans text-neutral-700 text-base md:text-lg leading-relaxed"
                >
                  {paragraph}
                </p>
              ))}
            </div>

            {/* Signature Quote */}
            <div className="border-l-4 border-gold pl-6 py-4 bg-neutral-50">
              <div className="mb-3">
                <Quote className="text-gold" size={32} />
              </div>
              <blockquote className="font-display text-navy text-xl md:text-2xl italic mb-4 leading-relaxed">
                "{quote}"
              </blockquote>
              <cite className="font-sans text-neutral-600 text-sm uppercase tracking-wider not-italic font-semibold">
                — {quoteAttribution}
              </cite>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
};

export default CandidateBioSplit;
