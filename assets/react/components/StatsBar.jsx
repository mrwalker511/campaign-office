import React from 'react';
import { motion } from 'framer-motion';
import { useInView } from 'framer-motion';
import { useRef } from 'react';

const StatItem = ({ value, label, delay = 0 }) => {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true });

  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 20 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.6, delay }}
      className="text-center px-6 py-4"
      data-testid="stat-item"
    >
      <div className="text-gold text-4xl md:text-5xl font-display font-bold mb-2">
        {value}
      </div>
      <div className="text-white/90 text-sm md:text-base font-sans uppercase tracking-[0.2em]">
        {label}
      </div>
    </motion.div>
  );
};

const StatsBar = ({
  stats = [
    { value: '25+', label: 'Years of Service' },
    { value: '98%', label: 'Pro-Constitution' },
    { value: '10,000+', label: 'Volunteers' },
    { value: '$2.5M', label: 'Grassroots Raised' }
  ]
}) => {
  return (
    <section className="bg-navy py-12 md:py-16">
      <div className="container mx-auto px-6">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 divide-x divide-gold/30">
          {stats.map((stat, index) => (
            <StatItem
              key={index}
              value={stat.value}
              label={stat.label}
              delay={index * 0.1}
            />
          ))}
        </div>
      </div>
    </section>
  );
};

export default StatsBar;
