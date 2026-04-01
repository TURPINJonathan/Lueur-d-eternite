'use client';

import { SendIcon } from 'lucide-react';
import { useMemo, useState } from 'react';
import { toast } from 'sonner';

import ButtonComponent from './Button.component';
import InputComponent from './Input.component';
import TextareaComponent from './Textarea.component';

export default function ContactFormComponent() {
  const [fullName, setFullName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [message, setMessage] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const isFormValid = useMemo(() => {
    const hasName = fullName.trim().length >= 2;
    const isEmailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
    const phoneDigits = phone.replace(/\D/g, '');
    const isPhoneValid = phoneDigits.length >= 10;
    const hasMessage = message.trim().length >= 10;

    return hasName && isEmailValid && isPhoneValid && hasMessage;
  }, [email, fullName, message, phone]);

  const emailInvalid = email.length > 0 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
  const phoneDigits = phone.replace(/\D/g, '');
  const phoneInvalid = phone.length > 0 && phoneDigits.length < 10;
  const nameInvalid = fullName.length > 0 && fullName.trim().length < 2;
  const messageInvalid = message.length > 0 && message.trim().length < 10;

  async function onSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!isFormValid || isSubmitting) return;

    setIsSubmitting(true);

    try {
      const base = (process.env.NEXT_PUBLIC_BACK_BASE_URL ?? '').replace(/\/+$/, '');
      const url = `${base}/api/public/contact`;

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          fullName: fullName.trim(),
          email: email.trim(),
          phone: phone.trim(),
          message: message.trim(),
          website: '', // honeypot
        }),
      });

      const payload = (await response.json().catch(() => ({}))) as {
        message?: string;
      };

      if (!response.ok) {
        throw new Error(payload.message ?? "L'envoi a échoué.");
      }

      toast.success(payload.message ?? 'Votre message a bien été envoyée.');
      setFullName('');
      setEmail('');
      setPhone('');
      setMessage('');
    } catch (error) {
      toast.error(error instanceof Error ? error.message : "L'envoi a échoué.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <form className="flex flex-col gap-4" noValidate onSubmit={onSubmit}>
      <div className="flex flex-col gap-2">
        <label htmlFor="fullName" className="!font-light">
          Nom et prénom *
        </label>
        <InputComponent
          id="fullName"
          name="fullName"
          type="text"
          required
          autoComplete="name"
          placeholder="Ex. Marie Dupont"
          value={fullName}
          onChange={(event) => setFullName(event.target.value)}
          aria-invalid={nameInvalid}
        />
      </div>

      <div className="flex flex-col gap-2">
        <label htmlFor="email" className="!font-light">
          Email *
        </label>
        <InputComponent
          id="email"
          name="email"
          type="email"
          required
          autoComplete="email"
          placeholder="Ex. marie.dupont@email.fr"
          value={email}
          onChange={(event) => setEmail(event.target.value)}
          aria-invalid={emailInvalid}
        />
      </div>

      <div className="flex flex-col gap-2">
        <label htmlFor="phone" className="!font-light">
          Téléphone *
        </label>
        <InputComponent
          id="phone"
          name="phone"
          type="tel"
          required
          autoComplete="tel"
          placeholder="Ex. 06 12 34 56 78"
          value={phone}
          onChange={(event) => setPhone(event.target.value)}
          aria-invalid={phoneInvalid}
        />
      </div>

      <div className="flex flex-col gap-2">
        <label htmlFor="message" className="!font-light">
          Votre message *
        </label>
        <TextareaComponent
          id="message"
          name="message"
          required
          rows={6}
          placeholder="Indiquez quelques détails sur votre demande : localisation de la sépulture, type d'intervention souhaitée, fréquence d'entretien, délai souhaité, etc."
          value={message}
          onChange={(event) => setMessage(event.target.value)}
          aria-invalid={messageInvalid}
        />
      </div>

      <ButtonComponent
        type="submit"
        variant={isFormValid && !isSubmitting ? 'gold' : 'goldSecondary'}
        outline={!isFormValid || isSubmitting}
        size="mdf"
        iconRight={<SendIcon className="h-5 w-5" />}
        disabled={!isFormValid || isSubmitting}
      >
        {isSubmitting ? 'Envoi en cours...' : 'Envoyer ma demande'}
      </ButtonComponent>

      <p className="text-center text-sm font-light italic">Les champs marqués d&apos;un * sont obligatoires</p>
    </form>
  );
}
