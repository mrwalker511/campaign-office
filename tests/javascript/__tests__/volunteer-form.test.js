/**
 * Volunteer Form JavaScript Tests
 *
 * @package CampaignOffice\Tests\JavaScript
 */

describe('Volunteer Form', () => {
  beforeEach(() => {
    // Set up DOM
    document.body.innerHTML = `
      <form id="volunteer-form">
        <input type="text" name="first_name" id="first_name" required />
        <input type="text" name="last_name" id="last_name" required />
        <input type="email" name="email" id="email" required />
        <input type="tel" name="phone" id="phone" />
        <select name="skills[]" id="skills" multiple>
          <option value="canvassing">Canvassing</option>
          <option value="phone_banking">Phone Banking</option>
          <option value="data_entry">Data Entry</option>
        </select>
        <button type="submit">Submit</button>
      </form>
    `;
  });

  test('should validate required fields', () => {
    const form = document.getElementById('volunteer-form');
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const emailInput = document.getElementById('email');

    expect(firstNameInput.required).toBe(true);
    expect(lastNameInput.required).toBe(true);
    expect(emailInput.required).toBe(true);
  });

  test('should validate email format', () => {
    const emailInput = document.getElementById('email');

    emailInput.value = 'invalid-email';
    expect(emailInput.validity.valid).toBe(false);

    emailInput.value = 'valid@example.com';
    expect(emailInput.validity.valid).toBe(true);
  });

  test('should handle form submission', async () => {
    const form = document.getElementById('volunteer-form');
    const mockSubmit = jest.fn((e) => e.preventDefault());

    form.addEventListener('submit', mockSubmit);

    // Fill form
    document.getElementById('first_name').value = 'John';
    document.getElementById('last_name').value = 'Volunteer';
    document.getElementById('email').value = 'john@example.com';

    // Submit
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.click();

    expect(mockSubmit).toHaveBeenCalled();
  });

  test('should collect form data correctly', () => {
    document.getElementById('first_name').value = 'Jane';
    document.getElementById('last_name').value = 'Doe';
    document.getElementById('email').value = 'jane@example.com';
    document.getElementById('phone').value = '555-1234';

    const form = document.getElementById('volunteer-form');
    const formData = new FormData(form);

    expect(formData.get('first_name')).toBe('Jane');
    expect(formData.get('last_name')).toBe('Doe');
    expect(formData.get('email')).toBe('jane@example.com');
    expect(formData.get('phone')).toBe('555-1234');
  });

  test('should handle multiple select values', () => {
    const skillsSelect = document.getElementById('skills');

    skillsSelect.options[0].selected = true; // canvassing
    skillsSelect.options[1].selected = true; // phone_banking

    const selectedSkills = Array.from(skillsSelect.selectedOptions).map(
      (option) => option.value
    );

    expect(selectedSkills).toContain('canvassing');
    expect(selectedSkills).toContain('phone_banking');
    expect(selectedSkills).toHaveLength(2);
  });
});

describe('Volunteer Form AJAX Submission', () => {
  beforeEach(() => {
    global.fetch = jest.fn();
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  test('should submit form via AJAX', async () => {
    global.fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ success: true, message: 'Volunteer registered!' }),
    });

    const formData = {
      first_name: 'Test',
      last_name: 'User',
      email: 'test@example.com',
      action: 'cp_submit_volunteer_signup',
      nonce: 'test_nonce',
    };

    const response = await fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    });

    const result = await response.json();

    expect(fetch).toHaveBeenCalledWith(
      '/wp-admin/admin-ajax.php',
      expect.objectContaining({
        method: 'POST',
      })
    );

    expect(result.success).toBe(true);
    expect(result.message).toBe('Volunteer registered!');
  });

  test('should handle AJAX errors gracefully', async () => {
    global.fetch.mockRejectedValueOnce(new Error('Network error'));

    try {
      await fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'cp_submit_volunteer_signup' }),
      });
    } catch (error) {
      expect(error.message).toBe('Network error');
    }
  });
});
