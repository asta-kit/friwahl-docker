#!/bin/sh

export FLOW_CONTEXT=Production

/data/friwahl/flow ballotbox:emit $1
