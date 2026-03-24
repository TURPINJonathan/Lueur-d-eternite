'use client';

import Link from 'next/link';
import { ReactNode } from 'react';

type ButtonVariant = 'primary' | 'secondary' | 'gold' | 'goldSecondary';
type ButtonSize = 'sm' | 'smf' | 'md' | 'mdf' | 'lg' | 'xl' | 'xlf';

interface CommonProps {
  children: ReactNode;
  variant?: ButtonVariant;
  size?: ButtonSize;
  iconLeft?: ReactNode;
  iconRight?: ReactNode;
  loading?: boolean;
  fullWidth?: boolean;
  className?: string;
  disabled?: boolean;
  outline?: boolean; // 👈 NEW
}

type ButtonAsButton = CommonProps & {
  href?: undefined;
  external?: never;
  onClick?: () => void;
  type?: 'button' | 'submit' | 'reset';
};

type ButtonAsLink = CommonProps & {
  href: string;
  external?: boolean;
  onClick?: never;
  type?: never;
};

type ButtonProps = ButtonAsButton | ButtonAsLink;

function cn(...classes: Array<string | false | null | undefined>) {
  return classes.filter(Boolean).join(' ');
}

function Spinner() {
  return (
    <span className="inline-block h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
  );
}

export default function Button(props: ButtonProps) {
  const {
    children,
    variant = 'primary',
    size = 'md',
    iconLeft,
    iconRight,
    loading = false,
    fullWidth = false,
    className,
    disabled = false,
    outline = false,
  } = props;

  const isDisabled = disabled || loading;

  const baseClasses = cn(
    'group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-[var(--radius-sm)]',
    'font-medium no-underline transition-all duration-300 ease-out',
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--ring)] focus-visible:ring-offset-2',
    fullWidth && 'w-full',
    isDisabled && 'pointer-events-none opacity-70',
  );

  const sizeClasses = {
    sm: 'min-h-10 px-4 text-sm',
    smf: 'min-h-10 px-4 text-sm w-full text-base',
    md: 'min-h-12 px-5 text-sm',
    mdf: 'min-h-12 px-5 text-sm w-full',
    lg: 'min-h-14 px-6 text-base',
    xl: 'min-h-16 px-8 text-base',
    xlf: 'min-h-16 w-full px-8 text-base',
  };

  // 🎨 FILLED
  const filledVariants = {
    primary: cn(
      'border border-[color-mix(in_srgb,var(--color-brand-secondary)_42%,transparent)]',
      'bg-[linear-gradient(135deg,color-mix(in_srgb,var(--color-brand-secondary)_30%,transparent),color-mix(in_srgb,var(--color-brand-primary)_42%,transparent))]',
      'text-[var(--color-white)] backdrop-blur-lg',
      'shadow-[0_14px_35px_rgba(63,92,110,0.18)]',
      'hover:-translate-y-0.5 hover:shadow-[0_18px_40px_rgba(63,92,110,0.24)]',
    ),

    secondary: cn(
      'border border-[color-mix(in_srgb,var(--color-white)_18%,transparent)]',
      'bg-[linear-gradient(135deg,rgba(255,255,255,0.10),rgba(255,255,255,0.05))]',
      'text-[var(--color-white)] backdrop-blur-lg',
      'shadow-[0_12px_28px_rgba(0,0,0,0.12)]',
      'hover:-translate-y-0.5',
    ),

    gold: 'button-gold',

    goldSecondary: cn(
      'border border-[rgba(212,175,55,0.28)]',
      'text-[rgba(255,248,220,0.96)]',
      'bg-[linear-gradient(135deg,rgba(212,175,55,0.10),rgba(168,132,47,0.16))]',
      'backdrop-blur-lg',
      'shadow-[0_10px_24px_rgba(212,175,55,0.10)]',
      'hover:-translate-y-0.5',
    ),
  };

  const outlineVariants = {
    primary: cn(
      'border border-[color-mix(in_srgb,var(--color-brand-secondary)_55%,transparent)]',
      'text-[var(--color-brand-secondary)]',
      'bg-transparent',
      'hover:bg-[color-mix(in_srgb,var(--color-brand-secondary)_8%,transparent)]',
    ),

    secondary: cn(
      'border border-[rgba(255,255,255,0.35)]',
      'text-white',
      'bg-transparent',
      'hover:bg-[rgba(255,255,255,0.08)]',
    ),

    gold: cn(
      'border border-[rgba(212,175,55,0.65)]',
      'text-[rgba(212,175,55,0.95)]',
      'bg-transparent',
      'hover:bg-[rgba(212,175,55,0.10)]',
      'hover:shadow-[0_0_20px_rgba(212,175,55,0.15)]',
    ),

    goldSecondary: cn(
      'border border-[rgba(212,175,55,0.35)]',
      'text-[rgba(168,132,47,0.9)]',
      'bg-transparent',
      'hover:bg-[rgba(212,175,55,0.08)]',
    ),
  };

  const variantClasses = outline ? outlineVariants[variant] : filledVariants[variant];

  const content = (
    <>
      {loading ? <Spinner /> : iconLeft ? <span className="flex shrink-0 items-center">{iconLeft}</span> : null}

      <span className="relative z-10">{children}</span>

      {!loading && iconRight ? <span className="flex shrink-0 items-center">{iconRight}</span> : null}
    </>
  );

  const classes = cn(baseClasses, sizeClasses[size], variantClasses, className);

  if ('href' in props && props.href) {
    if (props.external) {
      return (
        <a href={props.href} target="_blank" rel="noopener noreferrer" className={classes}>
          {content}
        </a>
      );
    }

    return (
      <Link href={props.href} className={classes}>
        {content}
      </Link>
    );
  }

  return (
    <button type={props.type ?? 'button'} onClick={props.onClick} disabled={isDisabled} className={classes}>
      {content}
    </button>
  );
}
