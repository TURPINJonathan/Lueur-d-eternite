export function sanitizePhoneToHref(phoneDisplay: string): string {
  const trimmed = phoneDisplay.trim();
  if (!trimmed) return '';

  let normalized = trimmed
    .replace(/[()\-\.\s]/g, '')
    .replace(/^00/, '+')
    .replace(/(?!^\+)[^\d]/g, '');

  if (!normalized.startsWith('+') && /^0\d{9}$/.test(normalized)) {
    normalized = `+33${normalized.slice(1)}`;
  }

  if (!normalized.startsWith('+')) {
    normalized = `+${normalized}`;
  }

  return normalized;
}
