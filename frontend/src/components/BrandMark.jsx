export default function BrandMark({ size = 28 }) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 32 32"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden="true"
      style={{
        display: 'block',
        borderRadius: 8,
        boxShadow: '0 4px 12px rgba(124, 58, 237, 0.32)',
      }}
    >
      <defs>
        <linearGradient id="brandmarkGrad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stopColor="#7C3AED" />
          <stop offset="100%" stopColor="#EC4899" />
        </linearGradient>
      </defs>
      <rect width="32" height="32" rx="8" fill="url(#brandmarkGrad)" />
      <rect x="7" y="9" width="18" height="13" rx="3" fill="#fff" />
      <polygon points="11,22 11,25.4 14.2,22" fill="#fff" />
      <circle cx="12" cy="15.5" r="1.4" fill="#7C3AED" />
      <circle cx="16" cy="15.5" r="1.4" fill="#7C3AED" />
      <circle cx="20" cy="15.5" r="1.4" fill="#7C3AED" />
    </svg>
  )
}
