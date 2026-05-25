import { useState, useEffect } from 'react'

export function useFetch(url, options = {}) {
  const [data, setData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    let isMounted = true

    const fetchData = async () => {
      try {
        setLoading(true)
        const token = localStorage.getItem('token')
        const headers = {
          ...options.headers,
          ...(token && { Authorization: `Bearer ${token}` })
        }

        const response = await fetch(url, {
          ...options,
          headers
        })

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }

        const json = await response.json()

        if (isMounted) {
          setData(json)
          setError(null)
        }
      } catch (error) {
        if (isMounted) {
          setError(error.message)
          setData(null)
        }
      } finally {
        if (isMounted) {
          setLoading(false)
        }
      }
    }

    fetchData()

    return () => {
      isMounted = false
    }
  }, [url, options])

  return { data, loading, error }
}
