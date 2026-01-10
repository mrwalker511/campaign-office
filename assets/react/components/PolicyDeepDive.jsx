import React from 'react';
import { motion } from 'framer-motion';
import { useInView } from 'framer-motion';
import { useRef } from 'react';

const PolicyItem = ({ number, title, description, delay = 0 }) => {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true });

  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, x: -30 }}
      animate={isInView ? { opacity: 1, x: 0 } : {}}
      transition={{ duration: 0.6, delay }}
      className="flex gap-6 md:gap-8 group"
      data-testid="policy-item"
    >
      {/* Number */}
      <div className="flex-shrink-0">
        <div className="w-16 h-16 md:w-20 md:h-20 border-2 border-gold flex items-center justify-center">
          <span className="font-display text-gold text-2xl md:text-3xl font-bold">
            {number.toString().padStart(2, '0')}
          </span>
        </div>
      </div>

      {/* Content */}
      <div className="flex-1 pb-12 border-b border-neutral-300 last:border-b-0 last:pb-0">
        <h3 className="font-display text-navy text-2xl md:text-3xl font-bold mb-4 group-hover:text-gold transition-colors duration-300">
          {title}
        </h3>
        <p className="font-sans text-neutral-700 text-base md:text-lg leading-relaxed">
          {description}
        </p>
      </div>
    </motion.div>
  );
};

const PolicyDeepDive = ({
  sectionTitle = 'Policy Platform',
  sectionSubtitle = 'Economic Recovery Plan',
  policies = [
    {
      title: 'Cut Taxes for Working Families',
      description: 'Reduce the tax burden on middle-class families by 15% while closing corporate loopholes. Every hardworking American should be able to keep more of what they earn.'
    },
    {
      title: 'Support Small Business Growth',
      description: 'Eliminate unnecessary regulations and provide targeted tax credits for small businesses that create local jobs. Our economy thrives when entrepreneurs can innovate without government interference.'
    },
    {
      title: 'Invest in Infrastructure',
      description: 'Direct federal funding to rebuild roads, bridges, and broadband networks in underserved areas. Strong infrastructure connects communities and drives economic growth.'
    },
    {
      title: 'Promote Energy Independence',
      description: 'Develop domestic energy resources while investing in next-generation technologies. America should never depend on foreign nations for our energy security.'
    },
    {
      title: 'Protect Social Security',
      description: 'Ensure our seniors receive the benefits they\'ve earned through a lifetime of hard work. We will fight any attempt to cut or privatize Social Security.'
    }
  ]
}) => {
  return (
    <section className="bg-white py-20 md:py-28">
      <div className="container mx-auto px-6 max-w-5xl">
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

        {/* Policy List */}
        <div className="space-y-12">
          {policies.map((policy, index) => (
            <PolicyItem
              key={index}
              number={index + 1}
              title={policy.title}
              description={policy.description}
              delay={index * 0.1}
            />
          ))}
        </div>
      </div>
    </section>
  );
};

export default PolicyDeepDive;
