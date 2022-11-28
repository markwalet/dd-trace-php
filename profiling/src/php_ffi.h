#include <SAPI.h>
#include <Zend/zend_extensions.h>
#include <Zend/zend_modules.h>
#include <php.h>
#include <stdbool.h>
#include <stddef.h>

#include <ext/standard/info.h>

// Profiling needs ZAI config for INI support.
#include <config/config.h>

// Used to communicate strings from C -> Rust.
#include <zai_string/string.h>

/* C11 allows a duplicate typedef provided they are the same, so this should be
 * fine as long as we compile with C11 or higher.
 */
typedef ZEND_RESULT_CODE zend_result;

/**
 * Returns macro expansion of ZEND_EXTENSION_BUILD_ID, which bindgen cannot
 * currently handle.
 */
const char *datadog_extension_build_id(void);

/**
 * Returns macro expansion of ZEND_MODULE_BUILD_ID, which bindgen cannot
 * currently handle.
 */
const char *datadog_module_build_id(void);

/**
 * Lookup module by name in the module registry. Returns NULL if not found.
 * This is meant to be called from Rust, so it uses types that are easy to use
 * in Rust. In Rust, strings are validated byte-slices instead of `char` slices
 * and array lengths use uintptr_t, not size_t.
 */
zend_module_entry *datadog_get_module_entry(const uint8_t *str, uintptr_t len);

/**
 * Fetches the VM interrupt address of the calling PHP thread.
 */
void *datadog_php_profiling_vm_interrupt_addr(void);

/**
 * For Code Hotspots, we need the tracer's local root span id and the current
 * span id. This is a cross-product struct, so keep it in sync with tracer's
 * version of this struct.
 * todo: re-use the tracer's header?
 */
typedef struct ddtrace_profiling_context_s {
    uint64_t local_root_span_id, span_id;
} ddtrace_profiling_context;

/**
 * A pointer to the tracer's ddtrace_get_profiling_context function if it was
 * found, otherwise points to a function which just returns {0, 0}.
 */
extern ddtrace_profiling_context (*datadog_php_profiling_get_profiling_context)(void);

/**
 * Called by this zend_extension's .startup handler. Does things that are
 * burdensome in Rust, like locating the ddtrace extension in the module
 * registry and finding the ddtrace_get_profiling_context function.
 */
void datadog_php_profiling_startup(zend_extension *extension);

/**
 * Used to hold information for overwriting the internal function handler
 * pointer in the Zend Engine.
 */
typedef struct {
    const char *name;
    size_t name_len;
    void (**old_handler)(INTERNAL_FUNCTION_PARAMETERS);
    void (*new_handler)(INTERNAL_FUNCTION_PARAMETERS);
} datadog_php_profiling_internal_function_handler;

void datadog_php_profiling_install_internal_function_handler(
    datadog_php_profiling_internal_function_handler handler);

/**
 * Copies the bytes represented by `view` into a zend_string, which is stored
 * in `dest`, passing `persistent` along so the right allocator is used.
 *
 * Does an empty string optimization.
 *
 * `dest` is expected to be uninitialized. Any existing content will not be
 * dtor'.
 */
void datadog_php_profiling_copy_string_view_into_zval(zval *dest, zai_string_view view,
                                                      bool persistent);

/**
 * Gets arg `n` of the `ex` frame. Returns NULL on error.
 * @see ZEND_FUNCTION(func_get_arg)
 */
zval *ddog_php_prof_zend_call_arg(zend_execute_data *ex, unsigned int n);
