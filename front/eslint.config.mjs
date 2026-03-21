import { defineConfig } from 'eslint/config';
import nextVitals from 'eslint-config-next/core-web-vitals';

const eslintConfig = defineConfig([
  // Global ignores first — plain object (no helper) for maximum reliability
  {
    ignores: ['.next/**', 'out/**', 'build/**', 'next-env.d.ts', 'node_modules/**', 'dist/**', 'old/**'],
  },

  // Next.js flat config (includes React, TypeScript parser, import, jsx-a11y, react-hooks)
  ...nextVitals,

  // General rules for all files
  {
    rules: {
      'no-console': [
        'error',
        {
          allow: [
            'log',
            'warn',
            'error',
            'dir',
            'assert',
            'clear',
            'count',
            'countReset',
            'group',
            'groupEnd',
            'table',
            'dirxml',
            'groupCollapsed',
            'profile',
            'profileEnd',
            'timeStamp',
          ],
        },
      ],
      'no-debugger': 'error',
      'no-empty': 'off',
      'prefer-const': 'error',
      eqeqeq: ['error', 'smart'],
    },
  },

  // TypeScript-specific rules
  // Plugin + parser already registered by nextVitals for *.ts/*.tsx
  {
    files: ['**/*.ts', '**/*.tsx'],
    rules: {
      '@typescript-eslint/consistent-type-definitions': ['error', 'interface'],
      '@typescript-eslint/explicit-member-accessibility': 'off',
      '@typescript-eslint/no-explicit-any': 'off',
      '@typescript-eslint/no-namespace': 'off',
      '@typescript-eslint/no-empty-function': 'off',
      '@typescript-eslint/no-inferrable-types': [
        'error',
        {
          ignoreParameters: true,
        },
      ],
      '@typescript-eslint/no-non-null-assertion': 'error',
      '@typescript-eslint/no-unused-vars': [
        'error',
        {
          argsIgnorePattern: '^_',
          varsIgnorePattern: '^_',
        },
      ],
    },
  },
]);

export default eslintConfig;
