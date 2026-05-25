import { Link, useLocation } from 'react-router-dom'

const menuItems = [
  { name: 'Dashboard', href: '/', icon: '📊' },
  { name: 'Parent Selection', href: '/parent-selection', icon: '👥' },
  { name: 'Data Entry', href: '/entry', icon: '📝' },
  { name: 'View Results', href: '/view-results', icon: '👁️' },
  { name: 'Reports', href: '/reports', icon: '📋' }
]

export default function Sidebar({ isOpen }) {
  const location = useLocation()

  return (
    <aside
      className={`fixed left-0 top-16 w-64 h-[calc(100vh-4rem)] bg-gray-900 text-white transition-all duration-300 ${
        isOpen ? 'translate-x-0' : '-translate-x-full'
      }`}
    >
      <nav className="p-4 space-y-2">
        {menuItems.map((item) => (
          <Link
            key={item.href}
            to={item.href}
            className={`block px-4 py-2 rounded-lg transition ${
              location.pathname === item.href
                ? 'bg-blue-600 text-white'
                : 'hover:bg-gray-800'
            }`}
          >
            <span className="mr-2">{item.icon}</span>
            {item.name}
          </Link>
        ))}
      </nav>
    </aside>
  )
}
