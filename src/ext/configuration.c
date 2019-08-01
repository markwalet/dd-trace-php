#include "configuration.h"

#include <stdlib.h>

#include "vendor_stdatomic.h"
#include "TSRM.h"
#include "env_config.h"

struct ddtrace_memoized_configuration_t ddtrace_memoized_configuration = {
#define CHAR(...) NULL, FALSE,
#define BOOL(...) FALSE, FALSE,
#define INT(...) 0, FALSE,
    DD_CONFIGURATION
#undef CHAR
#undef BOOL
#undef INT
        PTHREAD_MUTEX_INITIALIZER,
        ATOMIC_VAR_INIT(0),
        ATOMIC_VAR_INIT(0)
        };

void ddtrace_reload_config(COMPAT_CTX_D) {
#define CHAR(getter_name, env_name, default, ...)                                                     \
    pthread_mutex_lock(&ddtrace_memoized_configuration.mutex);                     \
    if (ddtrace_memoized_configuration.getter_name) {                              \
        free(ddtrace_memoized_configuration.getter_name); \
    } \
    ddtrace_memoized_configuration.getter_name = ddtrace_get_c_string_config_with_default(env_name, default COMPAT_CTX_CC); \
    ddtrace_memoized_configuration.__is_set_##getter_name = TRUE;                  \
    pthread_mutex_unlock(&ddtrace_memoized_configuration.mutex);
#define INT(getter_name, env_name, default, ...) \
    pthread_mutex_lock(&ddtrace_memoized_configuration.mutex);                                            \
    ddtrace_memoized_configuration.getter_name = ddtrace_get_int_config(env_name, default COMPAT_CTX_CC); \
    ddtrace_memoized_configuration.__is_set_##getter_name = TRUE;                                         \
    pthread_mutex_unlock(&ddtrace_memoized_configuration.mutex);
#define BOOL(getter_name, env_name, default, ...) \
    pthread_mutex_lock(&ddtrace_memoized_configuration.mutex);                                             \
    ddtrace_memoized_configuration.getter_name = ddtrace_get_bool_config(env_name, default COMPAT_CTX_CC); \
    ddtrace_memoized_configuration.__is_set_##getter_name = TRUE;                                          \
    pthread_mutex_unlock(&ddtrace_memoized_configuration.mutex);

    DD_CONFIGURATION

#undef CHAR
#undef INT
#undef BOOL
    // repopulate config
}

void ddtrace_reload_on_version_change(COMPAT_CTX_D) {

}

void ddtrace_initialize_config(COMPAT_CTX_D) {
    // read all values to memoize them

    // CHAR returns a copy of a value that we need to free
#define CHAR(getter_name, env_name, default, ...)                                      \
    pthread_mutex_lock(&ddtrace_memoized_configuration.mutex);                     \
    ddtrace_memoized_configuration.getter_name =                                   \
        ddtrace_get_c_string_config_with_default(env_name, default COMPAT_CTX_CC); \
    ddtrace_memoized_configuration.__is_set_##getter_name = TRUE;                  \
    pthread_mutex_unlock(&ddtrace_memoized_configuration.mutex);
#define INT(getter_name, env_name, default, ...)                                                              \
    pthread_mutex_lock(&ddtrace_memoized_configuration.mutex);                                            \
    ddtrace_memoized_configuration.getter_name = ddtrace_get_int_config(env_name, default COMPAT_CTX_CC); \
    ddtrace_memoized_configuration.__is_set_##getter_name = TRUE;                                         \
    pthread_mutex_unlock(&ddtrace_memoized_configuration.mutex);
#define BOOL(getter_name, env_name, default, ...)                                                              \
    pthread_mutex_lock(&ddtrace_memoized_configuration.mutex);                                             \
    ddtrace_memoized_configuration.getter_name = ddtrace_get_bool_config(env_name, default COMPAT_CTX_CC); \
    ddtrace_memoized_configuration.__is_set_##getter_name = TRUE;                                          \
    pthread_mutex_unlock(&ddtrace_memoized_configuration.mutex);

    DD_CONFIGURATION

#undef CHAR
#undef INT
#undef BOOL
}
