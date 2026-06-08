export const PASSWORD_REGEX =
  /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d])[A-Za-z\d\S]{8,128}$/;

export function validatePassword(password) {
  if (!PASSWORD_REGEX.test(password)) {
    return 'Passwort muss mindestens 8 Zeichen lang sein und Groß- und Kleinbuchstaben, eine Zahl sowie ein Sonderzeichen enthalten.';
  }
  return null;
}