import React from 'react';
import { motion } from 'framer-motion';
import { useInView } from 'framer-motion';
import { useRef } from 'react';
import { Flag, TrendingUp, Users } from 'lucide-react';

const ValueCard = ({ icon: Icon, title, description, delay = 0 }) => {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true });

  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 30 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.6, delay }}
      className="bg-white p-8 md:p-10 border-l-4 border-gold hover:shadow-xl transition-shadow duration-300"
      data-testid="value-card"
    >
      <div className="mb-6">
        <Icon className="text-navy" size={48} strokeWidth={1.5} />
      </div>
      <h3 className="font-display text-navy text-2xl md:text-3xl font-bold mb-4">
        {title}
      </h3>
      <p className="font-sans text-neutral-700 text-base md:text-lg leading-relaxed">
        {description}
      </p>
    </motion.div>
  );
};

const CoreValuesGrid = ({
  values = [
    {
      icon: Flag,
      title: 'Individual Liberty',
      description: 'Protecting constitutional rights and personal freedoms for every American citizen. Our nation was built on the principle that government exists to serve the people, not control them.'
    },
    {
      icon: TrendingUp,
      title: 'Economic Opportunity',
      description: 'Creating conditions for prosperity through free enterprise, fair taxation, and responsible governance. Every hardworking American deserves a chance to build a better future.'
    },
    {
      icon: Users,
      title: 'Strong Families',
      description: 'Supporting the bedrock of our society with policies that empower parents, strengthen communities, and preserve our values for future generations.'
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
            Core Values
          </motion.p>
          <motion.h2
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1 }}
            className="font-display text-navy text-4xl md:text-5xl lg:text-6xl font-bold"
          >
            What We Stand For
          </motion.h2>
        </div>

        {/* Values Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {values.map((value, index) => (
            <ValueCard
              key={index}
              icon={value.icon}
              title={value.title}
              description={value.description}
              delay={index * 0.2}
            />
          ))}
        </div>
      </div>
    </section>
  );
};

export default CoreValuesGrid;
