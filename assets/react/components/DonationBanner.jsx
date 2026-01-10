import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { DollarSign, Heart } from 'lucide-react';

const DonationBanner = ({
  title = 'Support Our Campaign',
  subtitle = 'Your contribution helps us reach more voters and spread our message',
  presetAmounts = [50, 100, 250, 500, 1000],
  onDonate
}) => {
  const [selectedAmount, setSelectedAmount] = useState(null);
  const [customAmount, setCustomAmount] = useState('');

  const handleDonate = () => {
    const amount = selectedAmount || parseFloat(customAmount);
    if (amount && onDonate) {
      onDonate(amount);
    }
  };

  const handlePresetClick = (amount) => {
    setSelectedAmount(amount);
    setCustomAmount('');
  };

  const handleCustomChange = (e) => {
    setCustomAmount(e.target.value);
    setSelectedAmount(null);
  };

  return (
    <section className="bg-red py-20 md:py-28">
      <div className="container mx-auto px-6 max-w-5xl">
        <div className="text-center mb-12">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="flex justify-center mb-6"
          >
            <Heart className="text-white" size={64} strokeWidth={1.5} />
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
            className="font-sans text-white/90 text-lg md:text-xl max-w-3xl mx-auto"
          >
            {subtitle}
          </motion.p>
        </div>

        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.3 }}
          className="bg-white p-8 md:p-12"
          data-testid="donation-form"
        >
          {/* Preset Amounts */}
          <div className="mb-8">
            <label className="block font-sans text-navy text-sm font-semibold uppercase tracking-wider mb-4">
              Select Amount
            </label>
            <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
              {presetAmounts.map((amount) => (
                <button
                  key={amount}
                  type="button"
                  onClick={() => handlePresetClick(amount)}
                  data-testid={`donate-${amount}`}
                  className={`
                    border-2 py-4 px-6 font-sans font-bold text-lg transition-all duration-200
                    ${selectedAmount === amount
                      ? 'border-gold bg-gold text-navy'
                      : 'border-neutral-300 text-navy hover:border-gold'
                    }
                  `}
                >
                  ${amount}
                </button>
              ))}
            </div>
          </div>

          {/* Custom Amount */}
          <div className="mb-8">
            <label htmlFor="custom-amount" className="block font-sans text-navy text-sm font-semibold uppercase tracking-wider mb-4">
              Or Enter Custom Amount
            </label>
            <div className="relative">
              <div className="absolute left-4 top-1/2 transform -translate-y-1/2">
                <DollarSign className="text-neutral-500" size={24} />
              </div>
              <input
                type="number"
                id="custom-amount"
                value={customAmount}
                onChange={handleCustomChange}
                min="1"
                step="1"
                data-testid="custom-amount"
                className="w-full border-2 border-neutral-300 pl-12 pr-4 py-4 font-sans text-navy text-lg focus:border-gold focus:outline-none transition-colors"
                placeholder="Enter amount"
              />
            </div>
          </div>

          {/* Donate Button */}
          <button
            type="button"
            onClick={handleDonate}
            data-testid="donate-submit"
            disabled={!selectedAmount && !customAmount}
            className="w-full bg-navy text-white px-12 py-5 font-sans font-bold text-lg tracking-[0.15em] uppercase hover:bg-navy-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Donate Now
          </button>

          {/* Legal Disclaimer */}
          <p className="text-neutral-600 text-xs font-sans text-center mt-6 leading-relaxed">
            Contributions are not tax-deductible. Federal law requires us to collect and report the name, mailing address, occupation, and employer of individuals whose contributions exceed $200 in an election cycle. Maximum contribution limit: $3,300 per individual per election.
          </p>
        </motion.div>
      </div>
    </section>
  );
};

export default DonationBanner;
