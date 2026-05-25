export default function Reports() {
  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-2">Reports</h1>
      <p className="text-gray-600 mb-8">View detailed transaction reports and analytics</p>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <ReportCard
          title="Transaction Statement"
          description="View complete transaction history"
          icon="📋"
        />
        <ReportCard
          title="Ledger Summary"
          description="Get a summary of ledger balances"
          icon="📊"
        />
        <ReportCard
          title="Monthly Report"
          description="Monthly transaction overview"
          icon="📅"
        />
        <ReportCard
          title="Export Data"
          description="Export reports in CSV/PDF format"
          icon="💾"
        />
      </div>
    </div>
  )
}

function ReportCard({ title, description, icon }) {
  return (
    <div className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition cursor-pointer">
      <div className="text-4xl mb-4">{icon}</div>
      <h3 className="text-lg font-semibold text-gray-900 mb-2">{title}</h3>
      <p className="text-gray-600 text-sm mb-4">{description}</p>
      <button className="text-blue-600 hover:text-blue-900 font-medium text-sm">
        Generate →
      </button>
    </div>
  )
}
