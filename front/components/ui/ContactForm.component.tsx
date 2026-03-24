'use client';

import { SendIcon } from 'lucide-react';
import { useMemo, useState } from 'react';

import ButtonComponent from './Button.component';
import InputComponent from './Input.component';
import TextareaComponent from './Textarea.component';

export default function ContactFormComponent() {
  const [fullName, setFullName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [message, setMessage] = useState('');

  const isFormValid = useMemo(() => {
    const hasName = fullName.trim().length >= 2;
    const isEmailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
    const phoneDigits = phone.replace(/\D/g, '');
    const isPhoneValid = phoneDigits.length >= 10;
    const hasMessage = message.trim().length >= 10;

    return hasName && isEmailValid && isPhoneValid && hasMessage;
  }, [email, fullName, message, phone]);

  return (
    <form className="flex flex-col gap-4">
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
        />
      </div>

      <ButtonComponent
        type="submit"
        variant={isFormValid ? 'gold' : 'goldSecondary'}
        outline={!isFormValid}
        size="mdf"
        iconRight={<SendIcon className="h-5 w-5" />}
        disabled={!isFormValid}
      >
        Envoyer ma demande
      </ButtonComponent>

      <p className="text-center text-sm font-light italic">Les champs marqués d&apos;un * sont obligatoires</p>
    </form>
  );
}
