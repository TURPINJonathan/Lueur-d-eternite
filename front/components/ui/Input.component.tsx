import { forwardRef, InputHTMLAttributes } from 'react';

type InputComponentProps = InputHTMLAttributes<HTMLInputElement>;

const InputComponent = forwardRef<HTMLInputElement, InputComponentProps>(function InputComponent(
  { className = '', ...props },
  ref,
) {
  return <input ref={ref} className={`input ${className}`.trim()} {...props} />;
});

export default InputComponent;
