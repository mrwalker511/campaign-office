/**
 * Sample JavaScript Test
 *
 * @package CampaignOffice\Tests
 */

import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

/**
 * Sample component for testing
 */
function Button({ onClick, children }) {
  return <button onClick={onClick}>{children}</button>;
}

describe('Sample JavaScript Tests', () => {
  test('should run basic assertions', () => {
    expect(true).toBe(true);
    expect(1 + 1).toBe(2);
    expect('hello').toBeTruthy();
  });

  test('should render a button', () => {
    render(<Button>Click me</Button>);
    const button = screen.getByRole('button', { name: /click me/i });
    expect(button).toBeInTheDocument();
  });

  test('should handle click events', async () => {
    const handleClick = jest.fn();
    const user = userEvent.setup();

    render(<Button onClick={handleClick}>Click me</Button>);
    const button = screen.getByRole('button', { name: /click me/i });

    await user.click(button);
    expect(handleClick).toHaveBeenCalledTimes(1);
  });

  test('should work with WordPress i18n', () => {
    const translated = wp.i18n.__('Hello World');
    expect(translated).toBe('Hello World');
  });
});

describe('Array and Object Tests', () => {
  test('should test arrays', () => {
    const arr = [1, 2, 3];
    expect(arr).toHaveLength(3);
    expect(arr).toContain(2);
    expect(arr).toEqual([1, 2, 3]);
  });

  test('should test objects', () => {
    const obj = { name: 'Campaign Office', version: '2.0' };
    expect(obj).toHaveProperty('name');
    expect(obj.name).toBe('Campaign Office');
    expect(obj).toMatchObject({ version: '2.0' });
  });
});

describe('Async Tests', () => {
  test('should handle promises', async () => {
    const promise = Promise.resolve('success');
    const result = await promise;
    expect(result).toBe('success');
  });

  test('should handle async functions', async () => {
    const fetchData = async () => {
      return { data: 'test' };
    };

    const result = await fetchData();
    expect(result).toEqual({ data: 'test' });
  });
});
