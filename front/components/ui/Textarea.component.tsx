import { forwardRef, TextareaHTMLAttributes } from 'react';

type TextareaComponentProps = TextareaHTMLAttributes<HTMLTextAreaElement>;

const TextareaComponent = forwardRef<HTMLTextAreaElement, TextareaComponentProps>(function TextareaComponent(
  { className = '', ...props },
  ref,
) {
  return <textarea ref={ref} className={`input ${className}`.trim()} {...props} />;
});

export default TextareaComponent;
