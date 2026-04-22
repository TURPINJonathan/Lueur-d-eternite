'use client';

import { SendIcon, Star } from 'lucide-react';
import { useMemo, useState } from 'react';
import { toast } from 'sonner';

import { ButtonComponent, InputComponent, TextareaComponent } from '#ui';

export default function ReviewForm() {
  const [author, setAuthor] = useState('');
  const [email, setEmail] = useState('');
  const [title, setTitle] = useState('');
  const [comment, setComment] = useState('');
  const [rate, setRate] = useState(5);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const isFormValid = useMemo(() => {
    const hasAuthor = author.trim().length >= 2;
    const isEmailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
    const hasComment = comment.trim().length >= 10;
    return hasAuthor && isEmailValid && hasComment;
  }, [author, comment, email]);

  const emailInvalid = email.length > 0 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
  const authorInvalid = author.length > 0 && author.trim().length < 2;
  const commentInvalid = comment.length > 0 && comment.trim().length < 10;

  async function onSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!isFormValid || isSubmitting) return;

    setIsSubmitting(true);

    try {
      const base = (process.env.NEXT_PUBLIC_BACK_BASE_URL ?? '').replace(/\/+$/, '');
      const url = `${base}/api/public/reviews`;

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          author: author.trim(),
          email: email.trim(),
          title: title.trim() ? title.trim() : null,
          comment: comment.trim(),
          rate,
        }),
      });

      const payload = (await response.json().catch(() => ({}))) as {
        message?: string;
        errors?: Record<string, string>;
      };

      if (!response.ok) {
        const firstFieldError = payload.errors ? Object.values(payload.errors)[0] : null;
        throw new Error(firstFieldError ?? payload.message ?? "L'envoi de l'avis a échoué.");
      }

      toast.success(payload.message ?? 'Votre avis a bien été envoyé.');
      setAuthor('');
      setEmail('');
      setTitle('');
      setComment('');
      setRate(5);
    } catch (error) {
      toast.error(error instanceof Error ? error.message : "L'envoi de l'avis a échoué.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <form className="flex flex-col gap-4 text-start" noValidate onSubmit={onSubmit}>
      <div className="flex flex-col gap-2">
        <label htmlFor="review-author" className="!font-light">
          Nom et prénom *
        </label>
        <InputComponent
          id="review-author"
          name="author"
          type="text"
          required
          autoComplete="name"
          placeholder="Ex. Marie Dupont"
          value={author}
          onChange={(event) => setAuthor(event.target.value)}
          aria-invalid={authorInvalid}
        />
      </div>

      <div className="flex flex-col gap-2">
        <label htmlFor="review-email" className="!font-light">
          Email *
        </label>
        <InputComponent
          id="review-email"
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
        <label htmlFor="review-title" className="!font-light">
          Titre
        </label>
        <InputComponent
          id="review-title"
          name="title"
          type="text"
          maxLength={120}
          placeholder="Ex. Intervention rapide et soignée"
          value={title}
          onChange={(event) => setTitle(event.target.value)}
        />
      </div>

      <div className="flex flex-col gap-2">
        <label htmlFor="review-comment" className="!font-light">
          Votre avis *
        </label>
        <TextareaComponent
          id="review-comment"
          name="comment"
          required
          rows={6}
          placeholder="Décrivez votre retour d'expérience (qualité, ponctualité, communication...)."
          value={comment}
          onChange={(event) => setComment(event.target.value)}
          aria-invalid={commentInvalid}
        />
      </div>

      <div className="flex flex-col items-center gap-2">
        <div className="flex items-center gap-8" role="radiogroup" aria-label="Note sur 5">
          {[1, 2, 3, 4, 5].map((value) => (
            <button
              key={value}
              type="button"
              onClick={() => setRate(value)}
              className="inline-flex items-center justify-center"
              aria-pressed={rate === value}
              aria-label={`${value} étoile${value > 1 ? 's' : ''}`}
            >
              <Star
                className={`h-6 w-6 cursor-pointer transition-colors ${value <= rate ? 'reviews-star--filled' : 'reviews-star--empty'}`}
              />
            </button>
          ))}
        </div>
      </div>

      <ButtonComponent
        type="submit"
        variant={isFormValid && !isSubmitting ? 'gold' : 'goldSecondary'}
        outline={!isFormValid || isSubmitting}
        size="mdf"
        iconRight={<SendIcon className="h-5 w-5" />}
        disabled={!isFormValid || isSubmitting}
      >
        {isSubmitting ? 'Envoi en cours...' : 'Envoyer mon avis'}
      </ButtonComponent>

      <p className="text-center text-sm font-light italic">Les champs marqués d&apos;un * sont obligatoires</p>
    </form>
  );
}
