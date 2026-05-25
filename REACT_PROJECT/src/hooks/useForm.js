import { useState } from 'react'
import { useForm } from 'react-hook-form'

export function useForm(options = {}) {
  const [submitError, setSubmitError] = useState(null)
  const form = useForm(options)

  const onSubmit = async (onSubmitFn) => {
    return form.handleSubmit(async (data) => {
      try {
        setSubmitError(null)
        await onSubmitFn(data)
      } catch (error) {
        setSubmitError(error.message)
      }
    })
  }

  return {
    ...form,
    onSubmit,
    submitError
  }
}
