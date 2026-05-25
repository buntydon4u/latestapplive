import { useEffect, useState } from 'react'
import { useAuth } from '@hooks/useAuth'

export default function Dashboard() {
  const { user } = useAuth()
  const [stats, setStats] = useState({
    totalLedgers: 0,
    recentEntries: 0,
    pendingApprovals: 0
  })

  useEffect(() => {
    // Fetch dashboard stats from API
    // const fetchStats = async () => {
    //   try {
    //     const response = await getDashboardStats()
    //     setStats(response.data)
    //   } catch (error) {
    //     console.error('Failed to fetch stats:', error)
    //   }
    // }
    // fetchStats()
  }, [])

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">Welcome, {user?.ledger_name}!</h1>
        <p className="text-gray-600 mt-2">Here's your transaction management dashboard</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {/* Stats Cards */}
        <StatCard
          title="Total Ledgers"
          value={stats.totalLedgers}
          icon="📊"
          color="blue"
        />
        <StatCard
          title="Recent Entries"
          value={stats.recentEntries}
          icon="📝"
          color="green"
        />
        <StatCard
          title="Pending Approvals"
          value={stats.pendingApprovals}
          icon="⏳"
          color="orange"
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Recent Activity */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h2>
          <div className="space-y-4">
            <p className="text-gray-500 text-center py-8">No recent activity</p>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
          <div className="space-y-3">
            <button className="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition">
              + New Entry
            </button>
            <button className="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg transition">
              View Results
            </button>
            <button className="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg transition">
              Generate Report
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}

function StatCard({ title, value, icon, color }) {
  const colors = {
    blue: 'bg-blue-50 text-blue-600',
    green: 'bg-green-50 text-green-600',
    orange: 'bg-orange-50 text-orange-600'
  }

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-gray-600 text-sm font-medium">{title}</p>
          <p className="text-3xl font-bold text-gray-900 mt-2">{value}</p>
        </div>
        <div className={`${colors[color]} p-4 rounded-lg text-2xl`}>
          {icon}
        </div>
      </div>
    </div>
  )
}
