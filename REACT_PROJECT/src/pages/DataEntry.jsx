export default function DataEntry() {
  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-2">Data Entry</h1>
      <p className="text-gray-600 mb-8">Add new transaction entries</p>

      <div className="bg-white rounded-lg shadow-md p-8">
        <div className="max-w-2xl mx-auto">
          <div className="space-y-6">
            {/* Entry Form */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Date
                </label>
                <input
                  type="date"
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Amount
                </label>
                <input
                  type="number"
                  placeholder="Enter amount"
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                rows="4"
                placeholder="Enter transaction description"
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>

            <div className="flex gap-4 pt-4">
              <button className="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition">
                Save Entry
              </button>
              <button className="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 py-2 px-4 rounded-lg transition">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
