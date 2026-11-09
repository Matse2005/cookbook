import { render, screen } from '@testing-library/react';
import { expect, it } from 'vite-plus/test';

import { Button } from '@/components/ui/button';

it('renders a shadcn button in jsdom', () => {
    render(<Button>Open cookbook</Button>);

    expect(screen.getByRole('button').textContent).toBe('Open cookbook');
});
