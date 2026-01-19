import React from 'react';
import { motion } from 'framer-motion';
import { useInView } from 'framer-motion';
import { useRef } from 'react';
import { Quote } from 'lucide-react';

const TestimonialCard = ({ quote, name, title, delay = 0 }) => {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true });

  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 30 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.6, delay }}
      className="bg-white p-8 md:p-10 border-l-4 border-gold"
      data-testid="testimonial-card"
    >
      <div className="mb-6">
        <Quote className="text-gold" size={40} />
      </div>
      <blockquote className="font-sans text-neutral-700 text-base md:text-lg leading-relaxed mb-8 italic">
        "&ldquo;{quote}&rdquo;"
      </blockquote>
      <div className="border-t-2 border-neutral-200 pt-6">
        <cite className="not-italic">
          <div className="font-display text-navy text-xl font-bold mb-1">
            {name}
          </div>
          <div className="font-sans text-neutral-600 text-sm uppercase tracking-wider">
            {title}
          </div>
        </cite>
      </div>
    </motion.div>
  );
};

const Testimonials = ({
  sectionTitle = 'What People Say',
  sectionSubtitle = 'Voices from the Community',
  testimonials = [
    {
      quote: 'John understands what small business owners face every day. He fought to cut red tape at the state level, and I know he\'ll do the same in Washington.',
      name: 'Maria Rodriguez',
      title: 'Small Business Owner'
    },
    {
      quote: 'As a veteran, I need leaders who will stand up for those who served. John has proven he has the courage and integrity to fight for us.',
      name: 'Robert Thompson',
      title: 'U.S. Army Veteran'
    },
    {
      quote: 'Our children deserve quality education and safe schools. John\'s record shows he puts students first, not special interests.',
      name: 'Jennifer Lee',
      title: 'Public School Teacher'
    }
  ]
}) => {
  return (
    <section className="bg-neutral-50 py-20 md:py-28">
      <div className="container mx-auto px-6 max-w-7xl">
        {/* Section Header */}
        <div className="text-center mb-16">
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="text-gold uppercase text-sm font-sans font-semibold tracking-[0.3em] mb-4"
          >
            {sectionTitle}
          </motion.p>
          <motion.h2
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1 }}
            className="font-display text-navy text-4xl md:text-5xl lg:text-6xl font-bold"
          >
            {sectionSubtitle}
          </motion.h2>
        </div>

        {/* Testimonials Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {testimonials.map((testimonial, index) => (
            <TestimonialCard
              key={index}
              quote={testimonial.quote}
              name={testimonial.name}
              title={testimonial.title}
              delay={index * 0.2}
            />
          ))}
        </div>
      </div>
    </section>
  );
};

export default Testimonials;
