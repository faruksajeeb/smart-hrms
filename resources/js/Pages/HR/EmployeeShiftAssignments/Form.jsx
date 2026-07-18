{/* Employee */}
                <div>
                    <label className="mb-2 block text-sm font-medium text-slate-700">
                        Employee
                    </label>
                    <select
                        value={data.employee_id}
                        onChange={(e) => setData("employee_id", e.target.value)}
                        className="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="">Select an employee</option>
                        {Object.entries(employees).map(([id, name]) => (
                            <option key={id} value={id}>
                                {name}
                            )
                        ))}
                    </select>
                    {errors.employee_id && <span className="mt-2 block text-sm text-red-600">{errors.employee_id}</span>}
                </div>